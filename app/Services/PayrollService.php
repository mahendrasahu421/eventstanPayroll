<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\AdvancePayment;
use App\Models\AdvanceRecovery;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Preview breakdown for payroll (no DB writes).
     */
    public function calculateBreakdownPreview(Employee $employee, string $month, array $inputs = []): array
    {
        // Normalize incoming numeric inputs (same keys as processEmployee)
        foreach ([
            'working_days', 'present_days', 'leave_days', 'overtime_hours',
            'food_deduction', 'visa_deduction', 'insurance_deduction',
            'advance_deduction', 'other_deduction',
            'wps_first_transfer',
        ] as $key) {
            if (array_key_exists($key, $inputs) && $inputs[$key] !== null) {
                $inputs[$key] = is_numeric($inputs[$key]) ? (float) $inputs[$key] : $inputs[$key];
            }
        }

        $salary = $employee->salaryStructure;

        if (! $salary) {
            throw new \Exception("No active salary structure for employee: {$employee->full_name}");
        }

        $workingDays = 30;
        $presentDays = isset($inputs['present_days']) && $inputs['present_days'] !== null
            ? (float) $inputs['present_days']
            : $workingDays;
        $leaveDays   = isset($inputs['leave_days']) && $inputs['leave_days'] !== null
            ? (float) $inputs['leave_days']
            : max(0, $workingDays - $presentDays);
        $overtimeHrs = $inputs['overtime_hours'] ?? 0;

        $attendanceRatio = $workingDays > 0 ? ($presentDays / $workingDays) : 1;

        $basicSalary        = round($salary->basic_salary * $attendanceRatio, 2);
        $housingAllowance   = round($salary->housing_allowance * $attendanceRatio, 2);
        $transportAllowance = round($salary->transport_allowance * $attendanceRatio, 2);
        $medicalAllowance   = round($salary->medical_allowance * $attendanceRatio, 2);
        $otherAllowance     = round($salary->other_allowance * $attendanceRatio, 2);
        $overtimeAmount     = round($overtimeHrs * $salary->overtime_rate_per_hour, 2);

        $grossSalary = $basicSalary + $housingAllowance + $transportAllowance
            + $medicalAllowance + $otherAllowance + $overtimeAmount;

        $foodDeductionInput      = $inputs['food_deduction'] ?? null;
        $visaDeductionInput      = $inputs['visa_deduction'] ?? null;
        $insuranceDeductionInput = $inputs['insurance_deduction'] ?? null;
        $otherDeductionInput     = $inputs['other_deduction'] ?? null;

        $foodDeduction = $foodDeductionInput !== null
            ? (float) $foodDeductionInput
            : round((float) $salary->food_deduction * $attendanceRatio, 2);

        $visaDeduction = $visaDeductionInput !== null
            ? (float) $visaDeductionInput
            : round((float) $salary->visa_deduction * $attendanceRatio, 2);

        $insuranceDeduction = $insuranceDeductionInput !== null
            ? (float) $insuranceDeductionInput
            : round((float) $salary->insurance_deduction * $attendanceRatio, 2);

        $otherDeductionValue = $otherDeductionInput !== null
            ? (float) $otherDeductionInput
            : round((float) ($salary->other_deduction ?? 0) * $attendanceRatio, 2);

        $advanceDeductionInput = $inputs['advance_deduction'] ?? null;
        $advanceDeduction = $advanceDeductionInput !== null
            ? (float) $advanceDeductionInput
            : $this->calculateAdvanceDeduction($employee, $month);

        $totalDeductions = $foodDeduction + $visaDeduction + $insuranceDeduction
            + $advanceDeduction + $otherDeductionValue;

        $netSalary = max(0, $grossSalary - $totalDeductions);

        $wpsFirstInput = $inputs['wps_first_transfer'] ?? null;
        $wpsBaseFirst = $salary->wps_first_transfer_amount ?: 0;

        $wpsFirstTransfer = $wpsFirstInput !== null
            ? min((float) $wpsFirstInput, $netSalary)
            : min((float) $wpsBaseFirst, $netSalary);

        $wpsSecondTransfer = max(0, $netSalary - $wpsFirstTransfer);

        $incrementAmount = $housingAllowance + $transportAllowance + $medicalAllowance + $otherAllowance;
        $totalMonthly = $basicSalary + $incrementAmount;
        $dailyRate = $workingDays > 0 ? round($totalMonthly / $workingDays, 2) : 0;

        return [
            'working_days'        => $workingDays,
            'present_days'        => $presentDays,
            'leave_days'          => $leaveDays,
            'overtime_hours'      => $overtimeHrs,

            'basic_salary'        => $basicSalary,
            'increment_amount'    => $incrementAmount,
            'total_monthly'       => $totalMonthly,
            'daily_rate'          => $dailyRate,
            'overtime_rate_per_hour' => (float) ($salary->overtime_rate_per_hour ?? 0),

            'housing_allowance'   => $housingAllowance,
            'transport_allowance' => $transportAllowance,
            'medical_allowance'   => $medicalAllowance,
            'other_allowance'     => $otherAllowance,
            'overtime_amount'     => $overtimeAmount,

            'gross_salary'        => $grossSalary,

            'food_deduction'      => $foodDeduction,
            'visa_deduction'      => $visaDeduction,
            'insurance_deduction' => $insuranceDeduction,
            'advance_deduction'   => $advanceDeduction,
            'other_deduction'     => $otherDeductionValue,

            'total_deductions'    => $totalDeductions,
            'net_salary'          => $netSalary,

            'wps_first_transfer'  => $wpsFirstTransfer,
            'wps_second_transfer' => $wpsSecondTransfer,
        ];
    }

    /**
     * Process payroll for a single employee for a given month.
     */
    public function processEmployee(Employee $employee, string $month, array $inputs = []): PayrollRecord
    {
        return DB::transaction(function () use ($employee, $month, $inputs) {
            $status = $inputs['status'] ?? 'processed';


            // Normalize incoming numeric inputs
            foreach ([
                'working_days', 'present_days', 'leave_days', 'overtime_hours',
                'food_deduction', 'visa_deduction', 'insurance_deduction',
                'advance_deduction', 'other_deduction',
                'wps_first_transfer',
            ] as $key) {
                if (array_key_exists($key, $inputs) && $inputs[$key] !== null) {
                    $inputs[$key] = is_numeric($inputs[$key]) ? (float) $inputs[$key] : $inputs[$key];
                }
            }


            $salary = $employee->salaryStructure;

            if (! $salary) {
                throw new \Exception("No active salary structure for employee: {$employee->full_name}");
            }

            // ── Attendance ────────────────────────────────────────────────────
            $workingDays  = 30;
            $presentDays  = isset($inputs['present_days']) && $inputs['present_days'] !== null
                ? (float) $inputs['present_days']
                : $workingDays;

            $leaveDays    = isset($inputs['leave_days']) && $inputs['leave_days'] !== null
                ? (float) $inputs['leave_days']
                : max(0, $workingDays - $presentDays);

            $overtimeHrs  = $inputs['overtime_hours'] ?? 0;

            // ── Per-day rate ──────────────────────────────────────────────────
            $dailyRate    = $salary->gross_salary / $workingDays;

            // ── Earnings ──────────────────────────────────────────────────────
            $attendanceRatio = $workingDays > 0 ? ($presentDays / $workingDays) : 1;

            $basicSalary        = round($salary->basic_salary        * $attendanceRatio, 2);
            $housingAllowance   = round($salary->housing_allowance   * $attendanceRatio, 2);
            $transportAllowance = round($salary->transport_allowance * $attendanceRatio, 2);
            $medicalAllowance   = round($salary->medical_allowance   * $attendanceRatio, 2);
            $otherAllowance     = round($salary->other_allowance     * $attendanceRatio, 2);
            $overtimeAmount     = round($overtimeHrs * $salary->overtime_rate_per_hour, 2);

            $grossSalary = $basicSalary + $housingAllowance + $transportAllowance
                         + $medicalAllowance + $otherAllowance + $overtimeAmount;

            // ── Deductions ────────────────────────────────────────────────────
            // Scale deductions sourced from salary structure using attendanceRatio.
            // If user overrides deductions in UI, do NOT scale.
            $foodDeductionInput      = $inputs['food_deduction'] ?? null;
            $visaDeductionInput      = $inputs['visa_deduction'] ?? null;
            $insuranceDeductionInput = $inputs['insurance_deduction'] ?? null;
            $otherDeductionInput     = $inputs['other_deduction'] ?? null;

            $foodDeduction = $foodDeductionInput !== null
                ? (float) $foodDeductionInput
                : round((float) $salary->food_deduction * $attendanceRatio, 2);

            $visaDeduction = $visaDeductionInput !== null
                ? (float) $visaDeductionInput
                : round((float) $salary->visa_deduction * $attendanceRatio, 2);

            $insuranceDeduction = $insuranceDeductionInput !== null
                ? (float) $insuranceDeductionInput
                : round((float) $salary->insurance_deduction * $attendanceRatio, 2);

            $otherDeductionValue = $otherDeductionInput !== null
                ? (float) $otherDeductionInput
                : round((float) ($salary->other_deduction ?? 0) * $attendanceRatio, 2);

            // Advance deduction (auto-calculated; not explicitly part of attendance scaling requirement)
            $advanceDeductionInput = $inputs['advance_deduction'] ?? null;
            $advanceDeduction = $advanceDeductionInput !== null
                ? (float) $advanceDeductionInput
                : $this->calculateAdvanceDeduction($employee, $month);

            $totalDeductions = $foodDeduction + $visaDeduction + $insuranceDeduction
                + $advanceDeduction + $otherDeductionValue;



            // ── Net & WPS ─────────────────────────────────────────────────────
            $netSalary = max(0, $grossSalary - $totalDeductions);

            $wpsFirstInput = $inputs['wps_first_transfer'] ?? null;
            $wpsBaseFirst  = $salary->wps_first_transfer_amount ?: 0;

            $wpsFirstTransfer = $wpsFirstInput !== null
                ? min((float) $wpsFirstInput, $netSalary)
                : min((float) $wpsBaseFirst, $netSalary);

            $wpsSecondTransfer = max(0, $netSalary - $wpsFirstTransfer);


            // ── Save / update record ──────────────────────────────────────────
            $record = PayrollRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'payroll_month' => $month],
                [
                    'working_days'        => $workingDays,
                    'present_days'        => $presentDays,
                    'leave_days'          => $leaveDays,
                    'overtime_hours'      => $overtimeHrs,
                    'basic_salary'        => $basicSalary,
                    'housing_allowance'   => $housingAllowance,
                    'transport_allowance' => $transportAllowance,
                    'medical_allowance'   => $medicalAllowance,
                    'other_allowance'     => $otherAllowance,
                    'overtime_amount'     => $overtimeAmount,
                    'gross_salary'        => $grossSalary,
                    'food_deduction'      => $foodDeduction,
                    'visa_deduction'      => $visaDeduction,
                    'insurance_deduction' => $insuranceDeduction,
                    'advance_deduction'   => $advanceDeduction,
                    'other_deduction'     => $otherDeductionValue,

                    'total_deductions'    => $totalDeductions,
                    'net_salary'          => $netSalary,
                    'wps_first_transfer'  => $wpsFirstTransfer,
                    'wps_second_transfer' => $wpsSecondTransfer,
                    'status'              => $status,
                    'processed_by'        => $status === 'processed' ? auth()->id() : null,
                    'processed_at'        => $status === 'processed' ? now() : null,

                ]
            );

            // ── Record advance recovery ───────────────────────────────────────
            if ($advanceDeduction > 0 && $status === 'paid') {
                $this->recordAdvanceRecovery($employee, $record, $advanceDeduction, $month);
            }


            return $record;
        });
    }

    /**
     * Bulk payroll run for all active employees.
     */
    public function bulkProcess(string $month, array $excludeEmployeeIds = []): array
    {
        $results = ['success' => [], 'failed' => []];

        $employees = Employee::active()
            ->whereHas('salaryStructure')
            ->whereNotIn('id', $excludeEmployeeIds)
            ->get();

        foreach ($employees as $employee) {
            try {
                $record = $this->processEmployee($employee, $month);
                $results['success'][] = [
                    'employee'   => $employee->full_name,
                    'employee_id'=> $employee->id,
                    'net_salary' => $record->net_salary,
                ];
            } catch (\Throwable $e) {
                $results['failed'][] = [
                    'employee'    => $employee->full_name,
                    'employee_id' => $employee->id,
                    'reason'      => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Calculate how much advance should be deducted this month.
     */
    private function calculateAdvanceDeduction(Employee $employee, string $month): float
    {
        $total = 0;

        foreach ($employee->activeAdvances as $advance) {
            $alreadyRecovered = AdvanceRecovery::where('advance_payment_id', $advance->id)
                ->where('recovery_month', $month)->exists();

            if ($alreadyRecovered) continue;

            $deductible = min($advance->installment_amount, $advance->pending_amount);
            $total += $deductible;
        }

        return round($total, 2);
    }

    /**
     * Record advance recovery entries and update advance status.
     */
    private function recordAdvanceRecovery(Employee $employee, PayrollRecord $record, float $amount, string $month): void
    {
        $remaining = $amount;

        foreach ($employee->activeAdvances as $advance) {
            if ($remaining <= 0) break;

            $deductible = min($advance->installment_amount, $advance->pending_amount, $remaining);
            if ($deductible <= 0) continue;

            AdvanceRecovery::create([
                'advance_payment_id' => $advance->id,
                'payroll_record_id'  => $record->id,
                'amount'             => $deductible,
                'recovery_month'     => $month,
            ]);

            $advance->increment('paid_installments');
            $advance->increment('recovered_amount', $deductible);
            $advance->decrement('pending_amount', $deductible);

            if ($advance->fresh()->pending_amount <= 0) {
                $advance->update(['status' => 'fully_recovered']);
            }

            $remaining -= $deductible;
        }
    }

    /**
     * Generate WPS export data for a given month.
     */
    public function generateWPSData(string $month): array
    {
        return PayrollRecord::with(['employee'])
            ->forMonth($month)
            ->whereIn('status', ['approved', 'paid'])
            ->get()
            ->map(fn($record) => [
                'employee_code'       => $record->employee->employee_code,
                'name'                => $record->employee->full_name,
                'wps_personal_number' => $record->employee->wps_personal_number,
                'bank_name'           => $record->employee->bank_name,
                'iban'                => $record->employee->iban,
                'first_transfer'      => $record->wps_first_transfer,
                'second_transfer'     => $record->wps_second_transfer,
                'net_salary'          => $record->net_salary,
            ])
            ->toArray();
    }

    /**
     * Monthly payroll summary for reports.
     */
    public function getMonthlySummary(string $month): array
    {
        $records = PayrollRecord::forMonth($month)->get();

        return [
            'month'            => $month,
            'total_employees'  => $records->count(),
            'total_gross'      => $records->sum('gross_salary'),
            'total_deductions' => $records->sum('total_deductions'),
            'total_net'        => $records->sum('net_salary'),
            'total_advances'   => $records->sum('advance_deduction'),
            'status_breakdown' => $records->groupBy('status')->map->count(),
        ];
    }
}
