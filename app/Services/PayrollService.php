<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\AdvancePayment;
use App\Models\AdvanceRecovery;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Resolve company-level working days for payroll calculations.
     */
    private function resolveWorkingDays(?string $month = null): int
    {
        $settings = CompanySetting::query()->first();
        $settingsDays = (int) ($settings?->working_days_per_month ?? 0);

        if ($settingsDays > 0) {
            return min(31, max(1, $settingsDays));
        }

        $payrollSettings = $settings?->payroll_settings ?? [];
        if ($month && isset($payrollSettings['working_days_by_month'][$month])) {
            $configured = (int) $payrollSettings['working_days_by_month'][$month];
            if ($configured > 0) {
                return min(31, max(1, $configured));
            }
        }

        return 30;
    }

    /**
     * Calculate payroll for a custom date range.
     */
    public function calculateDateRangeBreakdown(Employee $employee, Carbon $startDate, Carbon $endDate, array $inputs = []): array
    {
        if ($endDate->lt($startDate)) {
            throw new \InvalidArgumentException('End date must be on or after start date.');
        }

        $salary = $employee->salaryStructure;

        if (! $salary) {
            throw new \Exception("No active salary structure for employee: {$employee->full_name}");
        }

        $rangeDays = $startDate->diffInDays($endDate) + 1;
        $workingDays = $this->resolveWorkingDays($startDate->format('Y-m'));
        $presentDays = isset($inputs['present_days']) && $inputs['present_days'] !== null
            ? (float) $inputs['present_days']
            : (float) min($rangeDays, $workingDays);
        $leaveDays = isset($inputs['leave_days']) && $inputs['leave_days'] !== null
            ? (float) $inputs['leave_days']
            : max(0, $rangeDays - $presentDays);
        $overtimeHrs = (float) ($inputs['overtime_hours'] ?? 0);
        $attendanceRatio = $workingDays > 0 ? min(1, $presentDays / $workingDays) : 1;

        $basicSalary = round($salary->basic_salary * $attendanceRatio, 2);
        $housingAllowance = round($salary->housing_allowance * $attendanceRatio, 2);
        $transportAllowance = round($salary->transport_allowance * $attendanceRatio, 2);
        $medicalAllowance = round($salary->medical_allowance * $attendanceRatio, 2);
        $otherAllowance = round($salary->other_allowance * $attendanceRatio, 2);
        $overtimeAmount = round($overtimeHrs * $salary->overtime_rate_per_hour, 2);

        $grossSalary = $basicSalary + $housingAllowance + $transportAllowance + $medicalAllowance + $otherAllowance + $overtimeAmount;

        $foodDeduction = array_key_exists('food_deduction', $inputs) && $inputs['food_deduction'] !== null
            ? (float) $inputs['food_deduction']
            : round((float) $salary->food_deduction * $attendanceRatio, 2);

        $visaDeduction = array_key_exists('visa_deduction', $inputs) && $inputs['visa_deduction'] !== null
            ? (float) $inputs['visa_deduction']
            : $this->calculateVisaInstallmentDeduction($employee, $startDate->format('Y-m'));

        $insuranceDeduction = array_key_exists('insurance_deduction', $inputs) && $inputs['insurance_deduction'] !== null
            ? (float) $inputs['insurance_deduction']
            : round((float) $salary->insurance_deduction * $attendanceRatio, 2);

        $otherDeduction = array_key_exists('other_deduction', $inputs) && $inputs['other_deduction'] !== null
            ? (float) $inputs['other_deduction']
            : round((float) ($salary->other_deduction ?? 0) * $attendanceRatio, 2);

        $advanceDeduction = array_key_exists('advance_deduction', $inputs) && $inputs['advance_deduction'] !== null
            ? (float) $inputs['advance_deduction']
            : $this->calculateAdvanceDeduction($employee, $startDate->format('Y-m'));

        $totalDeductions = $foodDeduction + $visaDeduction + $insuranceDeduction + $advanceDeduction + $otherDeduction;
        $netSalary = max(0, $grossSalary - $totalDeductions);

        $wpsFirstInput = $inputs['wps_first_transfer'] ?? null;
        $wpsBaseFirst = $salary->wps_first_transfer_amount ?: 0;
        $wpsFirstTransfer = $wpsFirstInput !== null ? min((float) $wpsFirstInput, $netSalary) : min((float) $wpsBaseFirst, $netSalary);
        $wpsSecondTransfer = max(0, $netSalary - $wpsFirstTransfer);

        $incrementAmount = $housingAllowance + $transportAllowance + $medicalAllowance + $otherAllowance;
        $totalMonthly = $basicSalary + $incrementAmount;
        $dailyRate = $workingDays > 0 ? round($totalMonthly / $workingDays, 2) : 0;

        return [
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHrs,
            'basic_salary' => $basicSalary,
            'increment_amount' => $incrementAmount,
            'total_monthly' => $totalMonthly,
            'daily_rate' => $dailyRate,
            'overtime_rate_per_hour' => (float) ($salary->overtime_rate_per_hour ?? 0),
            'housing_allowance' => $housingAllowance,
            'transport_allowance' => $transportAllowance,
            'medical_allowance' => $medicalAllowance,
            'other_allowance' => $otherAllowance,
            'overtime_amount' => $overtimeAmount,
            'gross_salary' => $grossSalary,
            'food_deduction' => $foodDeduction,
            'visa_deduction' => $visaDeduction,
            'insurance_deduction' => $insuranceDeduction,
            'advance_deduction' => $advanceDeduction,
            'other_deduction' => $otherDeduction,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'wps_first_transfer' => $wpsFirstTransfer,
            'wps_second_transfer' => $wpsSecondTransfer,
            'range_start' => $startDate->toDateString(),
            'range_end' => $endDate->toDateString(),
        ];
    }

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

        $workingDays = $this->resolveWorkingDays($month);
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

        // Visa deduction now comes from visa installment advances.
        // We keep salaryStructure->visa_deduction as 0, but allow overrides from UI.
        $visaDeductionFromInstallments = $this->calculateVisaInstallmentDeduction($employee, $month);
        $visaDeduction = $visaDeductionInput !== null
            ? (float) $visaDeductionInput
            : $visaDeductionFromInstallments;

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
            $workingDays  = $this->resolveWorkingDays($month);
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
                : $this->calculateVisaInstallmentDeduction($employee, $month);


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
     * Calculate visa installment deduction for a month.
     * Visa is stored as AdvancePayment rows with reason containing 'Visa Charges (Installments)'.
     */
    private function calculateVisaInstallmentDeduction(Employee $employee, string $month): float
    {
        $total = 0.0;

        foreach ($employee->activeAdvances as $advance) {
            if (!is_string($advance->reason) || !str_contains($advance->reason, 'Visa Charges (Installments)')) {
                continue;
            }

            $alreadyRecovered = AdvanceRecovery::where('advance_payment_id', $advance->id)
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
