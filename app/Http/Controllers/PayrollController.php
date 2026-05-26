<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    /**
     * Bulk payroll page (Excel driven)
     */
    public function bulkForm()
    {
        return view('payroll.bulk');
    }

    // ---------------------------------------------------------------------
    // NOTE: Routes exist in routes/web.php for the methods below.
    // Your current PayrollController did not implement them, which causes
    // "Call to undefined method" errors and breaks payroll pages.
    // ---------------------------------------------------------------------

    public function downloadTemplate()
    {
        // Placeholder to prevent route crash.
        // Replace with real excel template download later.
        abort(501, 'downloadTemplate is not implemented yet');
    }

    public function customPaymentForm()
    {
        $employees = Employee::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        $month = now()->format('Y-m');

        return view('payroll.custom-payment', compact('employees', 'month'));
    }

    public function customPayment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payment_type' => 'required|in:full,partial,hold,release',
            'payment_scope' => 'required|in:month,range',

            'month' => 'nullable|date_format:Y-m',
            'months' => 'nullable|array|max:3',
            'months.*' => 'nullable|date_format:Y-m',

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'present_days' => 'nullable|numeric|min:0|max:31',
            'leave_days' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',

            'food_deduction' => 'nullable|numeric|min:0',
            'visa_deduction' => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'wps_first_transfer' => 'nullable|numeric|min:0',

            'partial_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $employee = Employee::with(['company', 'salaryStructure'])->findOrFail($validated['employee_id']);
        $company = $employee->company;

        $presentDays = isset($validated['present_days']) ? (float) $validated['present_days'] : 0;
        $leaveDays = isset($validated['leave_days']) ? (float) $validated['leave_days'] : 0;
        $overtimeHours = isset($validated['overtime_hours']) ? (float) $validated['overtime_hours'] : 0;

        // Convert scope into list of payroll months (Y-m)
        $months = [];
        if ($validated['payment_scope'] === 'month') {
            $primary = $validated['month'] ?? now()->format('Y-m');
            $months[] = $primary;
            foreach (($validated['months'] ?? []) as $m) {
                if (!empty($m)) {
                    $months[] = $m;
                }
            }
        } else {
            // range
            $start = $validated['start_date'] ? \Carbon\Carbon::parse($validated['start_date']) : now();
            $end = $validated['end_date'] ? \Carbon\Carbon::parse($validated['end_date']) : now();

            $cursor = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();

            while ($cursor->lte($endMonth)) {
                $months[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
        }

        $months = array_values(array_unique(array_filter($months)));
        sort($months);

        if (count($months) === 0) {
            return back()->with('error', 'No payroll month(s) selected.')->withInput();
        }

        $paymentType = $validated['payment_type'];

        // Status mapping
        // - hold  => draft
        // - release/full/partial => paid
        $targetStatus = match ($paymentType) {
            'hold' => 'draft',
            default => 'paid',
        };

        $partialAmount = isset($validated['partial_amount']) ? (float) $validated['partial_amount'] : 0;
        $remarks = $validated['remarks'] ?? null;

        $results = [
            'employee_id' => $employee->id,
            'months' => $months,
            'saved' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($months as $payrollMonth) {
                // Use leave_days only to influence present_days if present_days provided.
                $effectivePresentDays = $presentDays;
                if ($presentDays > 0 && $leaveDays > 0) {
                    $effectivePresentDays = max(0, $presentDays - $leaveDays);
                }

                $workingDays = (int) ($company?->working_days_per_month ?? 30);

                $calcInput = [
                    'employee_id' => $employee->id,
                    'working_days' => $workingDays,
                    'present_days' => $effectivePresentDays,
                    'overtime_hours' => $overtimeHours,
                    'food_deduction' => (float) ($validated['food_deduction'] ?? ($employee->salaryStructure?->food_deduction ?? 0)),
                    'visa_deduction' => (float) ($validated['visa_deduction'] ?? ($employee->salaryStructure?->visa_deduction ?? 0)),
                    'insurance_deduction' => (float) ($validated['insurance_deduction'] ?? ($employee->salaryStructure?->insurance_deduction ?? 0)),
                    'advance_deduction' => (float) ($validated['advance_deduction'] ?? $employee->salaryStructure?->advance_payment ?? 0),
                    'other_deduction' => (float) ($validated['other_deduction'] ?? 0),
                    'wps_first_transfer' => (float) ($validated['wps_first_transfer'] ?? ($employee->salaryStructure?->wps_first_transfer_amount ?? 0)),
                ];

                $payrollData = $this->calculatePayrollData($calcInput);
                $payrollData['payroll_month'] = $payrollMonth;
                $payrollData['status'] = $targetStatus;
                $payrollData['company_id'] = $company?->id;
                $payrollData['processed_at'] = in_array($targetStatus, ['paid', 'processed'], true) ? now() : null;

                // Apply partial logic only when payment_type=partial
                if ($paymentType === 'partial') {
                    // If partial_amount is not provided, fall back to full net salary.
                    if ($partialAmount <= 0) {
                        $partialAmount = (float) ($payrollData['net_salary'] ?? 0);
                    }

                    $originalNet = (float) ($payrollData['net_salary'] ?? 0);
                    $capNet = max(0, $originalNet);
                    $appliedNet = min($capNet, $partialAmount);

                    // Proportionally reduce split (keep first transfer ratio)
                    $origFirst = (float) ($payrollData['wps_first_transfer'] ?? 0);
                    $origSecond = (float) ($payrollData['wps_second_transfer'] ?? 0);
                    $origTotalSplit = $origFirst + $origSecond;

                    if ($origTotalSplit > 0) {
                        $scale = $appliedNet / $origTotalSplit;
                        $payrollData['wps_first_transfer'] = round($origFirst * $scale, 2);
                        $payrollData['wps_second_transfer'] = round($origSecond * $scale, 2);
                    } else {
                        $payrollData['wps_first_transfer'] = round(min((float) $payrollData['wps_first_transfer'] ?? 0, $appliedNet), 2);
                        $payrollData['wps_second_transfer'] = round(max(0, $appliedNet - (float) $payrollData['wps_first_transfer']), 2);
                    }

                    $payrollData['net_salary'] = round($appliedNet, 2);
                    $payrollData['status'] = 'paid';
                }

                $payrollRecordUpdate = [
                    'working_days' => $workingDays,
                    'present_days' => $effectivePresentDays,
                    'leave_days' => $leaveDays,
                    'overtime_hours' => $overtimeHours,
                    'remarks' => $remarks,
                    'processed_at' => $payrollData['processed_at'],
                    'status' => $payrollData['status'],
                ];

                PayrollRecord::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'payroll_month' => $payrollMonth,
                    ],
                    array_merge($payrollData, $payrollRecordUpdate, [
                        'payroll_month' => $payrollMonth,
                    ])
                );

                $results['saved'][] = $payrollMonth;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Custom payment error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Custom payment failed: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('payroll.history')
            ->with('success', 'Custom salary payment saved for ' . count($results['saved']) . ' month(s).');
    }


    public function reports(Request $request)
    {
        abort(501, 'reports is not implemented yet');
    }

    public function exportExcel(Request $request)
    {
        abort(501, 'exportExcel is not implemented yet');
    }

    public function wpsReport(Request $request)
    {
        abort(501, 'wpsReport is not implemented yet');
    }

    public function exportWPS(Request $request)
    {
        abort(501, 'exportWPS is not implemented yet');
    }

    /**
     * Bulk payroll processing (called by payroll/bulk page)
     */
    public function bulkProcess(Request $request)
    {
        $validated = $request->validate([
            'employees' => 'required|array',
            'employees.*.employee_id' => 'required',
            'payroll_month' => 'required|date_format:Y-m',
            'working_days' => 'required|numeric|min:1|max:31',
        ]);

        $month = $validated['payroll_month'];
        $employeePayloads = $validated['employees'];
        $workingDays = (int) $validated['working_days'];

        $processedCount = 0;
        $failed = [];

        foreach ($employeePayloads as $row) {
            try {
                $employeeId = $row['employee_id'] ?? $row['employeeId'] ?? null;
                if (! $employeeId) {
                    throw new \InvalidArgumentException('Missing employee_id');
                }

                $presentDays = (int) ($row['present_days'] ?? 0);
                $overtimeHours = (float) ($row['overtime_hours'] ?? 0);

                $this->calculateForEmployeeFromBulk($employeeId, $month, $workingDays, [
                    'present_days' => $presentDays,
                    'overtime_hours' => $overtimeHours,
                    'food_deduction' => (float) ($row['food_deduction'] ?? 0),
                    'other_deduction' => (float) ($row['other_deduction'] ?? 0),
                    'visa_deduction' => (float) ($row['visa_deduction'] ?? 0),
                    'insurance_deduction' => (float) ($row['insurance_deduction'] ?? 0),
                    'advance_deduction' => (float) ($row['advance_deduction'] ?? 0),
                    'wps_first_transfer' => (float) ($row['wps_first_transfer'] ?? 0),
                ]);

                $processedCount++;
            } catch (\Throwable $e) {
                $failed[] = [
                    'employee' => $row['employee_name'] ?? null,
                    'employee_id' => $row['employee_id'] ?? null,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'processed_count' => $processedCount,
            'failed_count' => count($failed),
            'failed' => $failed,
        ]);
    }

    private function calculateForEmployeeFromBulk(int $employeeId, string $month, int $workingDays, array $inputs): void
    {
        // Reuse existing payroll calculation logic by shaping Request-like array.
        $payload = array_merge($inputs, [
            'employee_id' => $employeeId,
            'month' => $month,
            'present_days' => $inputs['present_days'],
            'working_days' => $workingDays,
            'save_status' => 'paid',
            // calculate() expects company_id in request; derive from employee.
            'company_id' => Employee::query()->where('id', $employeeId)->value('company_id'),
        ]);

        // Directly call core calculator.
        $payrollData = $this->calculatePayrollData([
            'employee_id' => $employeeId,
            'basic_salary' => $inputs['basic_salary'] ?? null,
            'housing_allowance' => $inputs['housing_allowance'] ?? null,
            'transport_allowance' => $inputs['transport_allowance'] ?? null,
            'medical_allowance' => $inputs['medical_allowance'] ?? null,
            'other_allowance' => $inputs['other_allowance'] ?? null,
            'working_days' => $workingDays,
            'working_days_input' => $workingDays,
            'present_days' => $inputs['present_days'],
            'overtime_hours' => $inputs['overtime_hours'],
            'food_deduction' => $inputs['food_deduction'],
            'visa_deduction' => $inputs['visa_deduction'],
            'insurance_deduction' => $inputs['insurance_deduction'],
            'advance_deduction' => $inputs['advance_deduction'],
            'other_deduction' => $inputs['other_deduction'],
            'wps_first_transfer' => $inputs['wps_first_transfer'],
        ]);

        $payrollData['payroll_month'] = $month;
        $payrollData['status'] = 'paid';
        $payrollData['company_id'] = $payload['company_id'];
        $payrollData['processed_at'] = now();

        PayrollRecord::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'payroll_month' => $month,
            ],
            $payrollData + [
                'payroll_month' => $month,
            ]
        );
    }

    /**
     * Payroll history page
     */

    public function history(Request $request)
    {
        // Fallback implementation to avoid "Call to undefined method".
        // Keeps query consistent with payroll/history.blade.php expectations.
        $month = $request->query('month');
        $employeeId = $request->query('employee');
        $status = $request->query('status');

        $query = PayrollRecord::with(['employee'])
            ->orderByRaw('payroll_month DESC');

        if ($month) {
            // payroll_month stored as Y-m string
            $query->where('payroll_month', $month);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($status) {
            if ($status === 'processed') {
                $query->where('status', 'processed');
            } elseif ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif (in_array($status, ['draft', 'approved'], true)) {
                $query->where('status', $status);
            } else {
                $query->where('status', $status);
            }
        }

        $records = $query->paginate(25);

        $employees = Employee::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();


        return view('payroll.history', compact('records', 'employees'));
    }

    /**
     * Show the payroll process form with employees
     */
    public function processForm()

    {
        $employees = Employee::with(['company', 'department', 'designation', 'salaryStructure'])->get();
        return view('payroll.process', compact('employees'));
    }

    /**
     * Returns defaults used by resources/views/payroll/process.blade.php
     */
   /**
 * Returns defaults used by resources/views/payroll/process.blade.php
 */
/**
 * Returns defaults used by resources/views/payroll/process.blade.php
 */
public function employeeDefaults(int $id)
{
    try {
        $employee = Employee::with([
            'salaryStructure',
            'company'
        ])->findOrFail($id);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Employee not found'
        ], 404);
    }

    $salaryStructure = $employee->salaryStructure;
    $company = $employee->company;
    $month = now()->format('Y-m');
    
    // Get advance deduction directly from salary_structures table advance_payment column
    $advanceDeduction = (float) ($salaryStructure?->advance_payment ?? 0);
    $advanceDeduction = round($advanceDeduction, 2);

    // Get existing payroll record for current month
    $existingPayroll = PayrollRecord::where('employee_id', $employee->id)
        ->where('payroll_month', $month)
        ->first();

    return response()->json([
        'success' => true,
        'data' => [
            'food_deduction' => (float) ($salaryStructure?->food_deduction ?? 0),
            'visa_deduction' => (float) $this->calculateVisaInstallmentDeduction($employee, $month),
            'insurance_deduction' => (float) ($salaryStructure?->insurance_deduction ?? 0),
            'other_deduction' => (float) ($salaryStructure?->other_deduction ?? 0),
            'advance_deduction' => $advanceDeduction, // This will now show 150
            'wps_first_transfer' => (float) ($salaryStructure?->wps_first_transfer_amount ?? 0),
            'present_days' => (int) ($existingPayroll?->present_days ?? $employee->present_days ?? 30),
            'working_days_per_month' => (int) ($company?->working_days_per_month ?? 30),
            'overtime_hours' => (float) ($existingPayroll?->overtime_hours ?? $employee->overtime_hours ?? 0),
            'overtime_rate' => (float) ($salaryStructure?->overtime_rate_per_hour ?? $company?->overtime_rate ?? 0),
            'overtime_rate_per_hour' => (float) ($salaryStructure?->overtime_rate_per_hour ?? $company?->overtime_rate ?? 0),
            'basic_salary' => (float) ($salaryStructure?->basic_salary ?? 0),
            'total_monthly' => (float) (
                ($salaryStructure?->basic_salary ?? 0) +
                ($salaryStructure?->housing_allowance ?? 0) +
                ($salaryStructure?->transport_allowance ?? 0) +
                ($salaryStructure?->medical_allowance ?? 0) +
                ($salaryStructure?->other_allowance ?? 0)
            ),
        ],
    ]);
}
    /**
     * Get complete employee details with company, salary structure, and deductions
     */
    public function getEmployeeDetails($id)
    {
        try {
            $employee = Employee::with([
                'company',
                'department',
                'designation',
                'salaryStructure',
                'advances' => function ($query) {
                    $query->where('status', 'active')
                        ->where('pending_amount', '>', 0);
                }
            ])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }


        // Calculate monthly advance deduction if any
        $monthlyAdvanceDeduction = 0;
        foreach ($employee->advances as $advance) {
            if ($advance->installment_amount > 0) {
                $monthlyAdvanceDeduction += $advance->installment_amount;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                // Employee Basic Info
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'joining_date' => $employee->joining_date,
                'department_name' => $employee->department?->name,
                'designation_name' => $employee->designation?->name,
                'status' => $employee->status,

                // Company Details
                'company' => [
                    'id' => $employee->company?->id,
                    'name' => $employee->company?->company_name,
                    'currency_symbol' => $employee->company?->currency_symbol ?? 'AED',
                    'currency_code' => $employee->company?->currency ?? 'AED',
                    'overtime_rate' => $employee->company?->overtime_rate ?? 1.5,
                    'working_days_per_month' => $employee->company?->working_days_per_month ?? 30,
                ],

                // Salary Structure (from employee creation)
                'salary_structure' => [
                    'basic_salary' => $employee->salaryStructure?->basic_salary ?? 0,
                    'housing_allowance' => $employee->salaryStructure?->housing_allowance ?? 0,
                    'transport_allowance' => $employee->salaryStructure?->transport_allowance ?? 0,
                    'medical_allowance' => $employee->salaryStructure?->medical_allowance ?? 0,
                    'other_allowance' => $employee->salaryStructure?->other_allowance ?? 0,
                    'overtime_rate_per_hour' => $employee->salaryStructure?->overtime_rate_per_hour ?? 0,
                    'wps_first_transfer_amount' => $employee->salaryStructure?->wps_first_transfer_amount ?? 0,
                ],

                // Fixed Deductions (from employee creation)
                'deductions' => [
                    'food_deduction' => $employee->salaryStructure?->food_deduction ?? 0,
                    'visa_deduction' => $employee->salaryStructure?->visa_deduction ?? 0,
                    'insurance_deduction' => $employee->salaryStructure?->insurance_deduction ?? 0,
                ],

                // Monthly Advance Deduction
                'monthly_advance_deduction' => $monthlyAdvanceDeduction,

                // Previous Payroll for this month (if exists)
'existing_payroll' => PayrollRecord::where('employee_id', $employee->id)
                    ->where('payroll_month', request()->month ?? now()->format('Y-m'))
                    ->first(),
            ]
        ]);
    }

    /**
     * Generate a real-time preview of the payroll calculation
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
     * Calculate and store the final payroll record
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
            'present_days' => 'required|numeric|min:0|max:31',
            'save_status' => 'required|in:draft,paid'
        ]);

        DB::beginTransaction();
        try {
            $payrollData = $this->calculatePayrollData($request->all());
$payrollData['payroll_month'] = $request->month;
            $payrollData['status'] = $request->save_status;
            $payrollData['company_id'] = $request->company_id;
            $payrollData['processed_at'] = now();

PayrollRecord::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'payroll_month' => $request->month,
                ],
                $payrollData + [
                    'payroll_month' => $request->month,
                ]
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
     * Core business logic for payroll calculation
     */
   private function calculatePayrollData(array $input)
{
    $employee = Employee::with(['salaryStructure', 'company'])->findOrFail($input['employee_id']);
    $company = $employee->company;
  
    // Get values from input or from employee's saved data
    $basicSalary = (float) ($input['basic_salary'] ?? $employee->salaryStructure?->basic_salary ?? 0);
    $totalMonthly = $basicSalary;

    // Add allowances if any
    $totalMonthly += (float) ($input['housing_allowance'] ?? $employee->salaryStructure?->housing_allowance ?? 0);
    $totalMonthly += (float) ($input['transport_allowance'] ?? $employee->salaryStructure?->transport_allowance ?? 0);
    $totalMonthly += (float) ($input['medical_allowance'] ?? $employee->salaryStructure?->medical_allowance ?? 0);
    $totalMonthly += (float) ($input['other_allowance'] ?? $employee->salaryStructure?->other_allowance ?? 0);

    // Working days and attendance (dynamic from company if input empty)
    $workingDaysInput = $input['working_days'] ?? null;
    $workingDays = (int) (
        ($workingDaysInput !== null && $workingDaysInput !== '')
        ? $workingDaysInput
        : ($company?->working_days_per_month ?? 30)
    );

    // Ensure present_days is always numeric (UI validates max 31)
    $presentDays = (float) ($input['present_days'] ?? 0);
    if ($presentDays < 0)
        $presentDays = 0;
    if ($workingDays > 0 && $presentDays > $workingDays)
        $presentDays = (float) $workingDays;

    $dailyRate = $workingDays > 0 ? ($totalMonthly / $workingDays) : 0;
    $daysWorkedAmount = $dailyRate * $presentDays;

    // Overtime calculation (dynamic from company rate if not provided)
    $overtimeHours = (float) ($input['overtime_hours'] ?? 0);
    if ($overtimeHours < 0)
        $overtimeHours = 0;

    $companyOvertimeRate = (float) ($company?->overtime_rate ?? 1.5);
    $hourlyRate = $dailyRate > 0 ? ($dailyRate / 8) : 0;
    $overtimeAmount = $overtimeHours * ($hourlyRate * $companyOvertimeRate);

    $grossSalary = $daysWorkedAmount + $overtimeAmount;

    // Deductions - Get advance from salary_structure if not provided in input
    $foodDeduction = (float) ($input['food_deduction'] ?? $employee->salaryStructure?->food_deduction ?? 0);
    $visaDeduction = (float) ($input['visa_deduction'] ?? $employee->salaryStructure?->visa_deduction ?? 0);
    $insuranceDeduction = (float) ($input['insurance_deduction'] ?? $employee->salaryStructure?->insurance_deduction ?? 0);
    
    // Priority: input value > salary_structure advance_payment
    $advanceDeduction = (float) ($input['advance_deduction'] ?? $employee->salaryStructure?->advance_payment ?? 0);
    
    $otherDeduction = (float) ($input['other_deduction'] ?? 0);

    $totalDeductions = $foodDeduction + $visaDeduction + $insuranceDeduction + $advanceDeduction + $otherDeduction;
    $netSalary = $grossSalary - $totalDeductions;

    // WPS splitting
    $wpsFirstTransfer = (float) ($input['wps_first_transfer'] ?? $employee->salaryStructure?->wps_first_transfer_amount ?? 0);
    if ($wpsFirstTransfer > $netSalary) {
        $wpsFirstTransfer = $netSalary;
    }
    $wpsSecondTransfer = max(0, $netSalary - $wpsFirstTransfer);

    return [
        'employee_id' => $employee->id,
        'company_id' => $company?->id,
        'currency_symbol' => $company?->currency_symbol ?? 'AED',
        'basic_salary' => round($basicSalary, 2),
        'total_monthly' => round($totalMonthly, 2),
        'working_days' => $workingDays,
        'present_days' => $presentDays,
        'daily_rate' => round($dailyRate, 2),
        'days_worked_amount' => round($daysWorkedAmount, 2),
        'overtime_hours' => $overtimeHours,
        'overtime_rate' => round($companyOvertimeRate, 2),
        'overtime_amount' => round($overtimeAmount, 2),
        'gross_salary' => round($grossSalary, 2),
        'food_deduction' => round($foodDeduction, 2),
        'visa_deduction' => round($visaDeduction, 2),
        'insurance_deduction' => round($insuranceDeduction, 2),
        'advance_deduction' => round($advanceDeduction, 2),
        'other_deduction' => round($otherDeduction, 2),
        'total_deductions' => round($totalDeductions, 2),
        'net_salary' => round($netSalary, 2),
        'wps_first_transfer' => round($wpsFirstTransfer, 2),
        'wps_second_transfer' => round($wpsSecondTransfer, 2),
    ];
}

    /**
     * Calculate visa installment deduction for a month.
     * Visa is stored as AdvancePayment rows with reason containing 'Visa Charges (Installments)'.
     */
    private function calculateVisaInstallmentDeduction(Employee $employee, string $month): float
    {
        // Ensure activeAdvances relationship is available.
        if (!method_exists($employee, 'activeAdvances')) {
            return 0.0;
        }

        $total = 0.0;

        foreach ($employee->activeAdvances as $advance) {
            if (!is_string($advance->reason) || !str_contains($advance->reason, 'Visa Charges (Installments)')) {
                continue;
            }

            $alreadyRecovered = \App\Models\AdvanceRecovery::where('advance_payment_id', $advance->id)
                ->where('recovery_month', $month)
                ->exists();

            if ($alreadyRecovered) {
                continue;
            }

            $deductible = min((float) $advance->installment_amount, (float) $advance->pending_amount);
            $total += $deductible;
        }

        return round($total, 2);
    }

    /**
     * Salary slip view for a given payroll record.
     */
    public function salarySlip(int $record)

    {
        $payrollRecord = PayrollRecord::with([
            'employee' => function ($q) {
                $q->with(['company', 'department', 'designation']);
            }
        ])->findOrFail($record);

        $company = $payrollRecord->employee?->company;

        return view('payroll.salary-slip', [
            'record' => $payrollRecord,
            'company' => $company,
        ]);
    }

    /**
     * Toggle/update payroll record status from payroll/history page
     */
    public function updateStatus(Request $request, $recordId)
    {
        $request->validate([
            'status' => 'required|string|in:paid,processed,draft,approved',
        ]);

        $payrollRecord = PayrollRecord::query()->findOrFail($recordId);

        $newStatus = $request->input('status');
        $payrollRecord->status = $newStatus;

        // Mark processed_at when transitioning to a paid/processed state
        if (in_array($newStatus, ['paid', 'processed'], true)) {
            $payrollRecord->processed_at = $payrollRecord->processed_at ?? now();
        }

        $payrollRecord->save();

        return redirect()->route('payroll.history')->with('success', 'Payroll status updated successfully.');
    }
}


