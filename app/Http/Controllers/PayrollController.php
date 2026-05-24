<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    /**
     * Fetch default deduction settings for an employee to pre-fill the form.
     */
    public function getEmployeeDefaults($id)
    {
        $employee = Employee::with(['department', 'designation'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'food_deduction' => $employee->food_allowance_deduction ?? 0,
                'visa_deduction' => $employee->visa_charge_deduction ?? 0,
                'insurance_deduction' => $employee->insurance_deduction ?? 0,
                'other_deduction' => 0,
                'wps_first_transfer' => $employee->wps_limit ?? 0,
                // In a production environment, you would calculate total outstanding 
                // advances/loans for the selected month here.
                'advance_deduction' => 0, 
            ]
        ]);
    }

    /**
     * Generate a real-time preview of the payroll calculation for the UI.
     */
    public function previewBreakdown(Request $request)
    {
        try {
            $data = $this->calculatePayrollData($request->all());
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Payroll Preview Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Calculation failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Calculate and store the final payroll record.
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'month'        => 'required|date_format:Y-m',
            'present_days' => 'required|numeric|min:0|max:31',
            'save_status'  => 'required|in:draft,paid'
        ]);

        DB::beginTransaction();
        try {
            $payrollData = $this->calculatePayrollData($request->all());
            $payrollData['month'] = $request->month;
            $payrollData['status'] = $request->save_status;
            $payrollData['processed_at'] = now();

            // Update existing record for the same month or create a new one
            Payroll::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'month'       => $request->month,
                ],
                $payrollData
            );

            DB::commit();
            return redirect()->route('payroll.history')->with('success', 'Payroll processed and saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll Processing Error: ' . $e->getMessage());
            return back()->with('error', 'Calculation Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Core business logic for payroll calculation.
     */
    private function calculatePayrollData(array $input)
    {
        $employee = Employee::findOrFail($input['employee_id']);
        
        // 1. Base Compensation
        $basicSalary = (float) $employee->basic_salary;
        $incrementAmount = (float) ($employee->increment_amount ?? 0);
        $totalMonthly = $basicSalary + $incrementAmount;
        
        // 2. Pro-rated Salary calculation
        $workingDays = (int) ($input['working_days'] ?? 30);
        $presentDays = (float) ($input['present_days'] ?? 0);
        $dailyRate = $workingDays > 0 ? ($totalMonthly / $workingDays) : 0;
        $daysWorkedAmount = $dailyRate * $presentDays;
        
        // 3. Overtime calculation (Standard logic: 1.5x hourly rate)
        // Formula: (Daily Rate / 8 hours) * 1.5
        $overtimeHours = (float) ($input['overtime_hours'] ?? 0);
        $overtimeRate = $dailyRate > 0 ? (($dailyRate / 8) * 1.5) : 0;
        $overtimeAmount = $overtimeHours * $overtimeRate;
        
        $grossSalary = $daysWorkedAmount + $overtimeAmount;
        
        // 4. Deductions
        $foodDeduction = (float) ($input['food_deduction'] ?? 0);
        $visaDeduction = (float) ($input['visa_deduction'] ?? 0);
        $insuranceDeduction = (float) ($input['insurance_deduction'] ?? 0);
        $advanceDeduction = (float) ($input['advance_deduction'] ?: 0); 
        $otherDeduction = (float) ($input['other_deduction'] ?? 0);
        
        $totalDeductions = $foodDeduction + $visaDeduction + $insuranceDeduction + $advanceDeduction + $otherDeduction;
        $netSalary = $grossSalary - $totalDeductions;
        
        // 5. WPS Salary Splitting
        $wpsFirstTransfer = (float) ($input['wps_first_transfer'] ?: ($employee->wps_limit ?? 0));
        
        // Safeguard: WPS transfer cannot exceed the total net payable amount
        if ($wpsFirstTransfer > $netSalary) {
            $wpsFirstTransfer = $netSalary;
        }
        
        $wpsSecondTransfer = max(0, $netSalary - $wpsFirstTransfer);

        return [
            'employee_id'         => $employee->id,
            'basic_salary'        => $basicSalary,
            'increment_amount'    => $incrementAmount,
            'total_monthly'       => $totalMonthly,
            'working_days'        => $workingDays,
            'present_days'        => $presentDays,
            'daily_rate'          => round($dailyRate, 2),
            'days_worked_amount'  => round($daysWorkedAmount, 2),
            'overtime_hours'      => $overtimeHours,
            'overtime_rate'       => round($overtimeRate, 2),
            'overtime_amount'     => round($overtimeAmount, 2),
            'gross_salary'        => round($grossSalary, 2),
            'food_deduction'      => $foodDeduction,
            'visa_deduction'      => $visaDeduction,
            'insurance_deduction' => $insuranceDeduction,
            'advance_deduction'   => $advanceDeduction,
            'other_deduction'     => $otherDeduction,
            'total_deductions'    => round($totalDeductions, 2),
            'net_salary'          => round($netSalary, 2),
            'wps_first_transfer'  => round($wpsFirstTransfer, 2),
            'wps_second_transfer' => round($wpsSecondTransfer, 2),
        ];
    }
}