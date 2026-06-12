<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Country;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class EmployeesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    private int $rowCount = 0;
    private array $errors = [];

    public function model(array $row): ?Employee
    {
        // Bulk header uses `Employee Name` (single value like "John Doe"), not `first_name/last_name`
        $employeeNameCheck = $this->getRowValue($row, ['employee_name', 'Employee Name']);
        if (empty(trim((string) $employeeNameCheck))) return null;

        if (!empty($row['email']) && Employee::where('email', $row['email'])->exists()) {
            $this->errors[] = "Row skipped: Email {$row['email']} already exists.";
            return null;
        }

        $departmentId  = null;
        $designationId = null;

        // ✅ Pass company_code bhi resolveCompanyId mein
        $companyId = $this->resolveCompanyId(
            $row['company']      ?? $row['company_name'] ?? null,
            $row['company_code'] ?? null,
            $row['company_id']   ?? null
        );

        $nationality = $this->getRowValue($row, ['nationality', 'Nationality']);
        $dateOfBirth = $this->parseDate($this->getRowValue($row, ['dob', 'DOB', 'date_of_birth', 'Date of Birth']));


        if (!empty($row['department'])) {
            $dept = Department::firstOrCreate(
                ['name' => trim($row['department'])],
                ['is_active' => true]
            );
            $departmentId = $dept->id;
        }

        if (!empty($row['designation']) && $departmentId) {
            $desig = Designation::firstOrCreate(
                ['name' => trim($row['designation']), 'department_id' => $departmentId],
                ['is_active' => true]
            );
            $designationId = $desig->id;
        }

        $this->rowCount++;

        // ─── Insurance fields ────────────────────────────────────────────
        $insuranceProviderValue = $this->getRowValue($row, [
            'insurance_provider', 'Insurance provider', 'Insurance Provider',
        ]);

        $insurancePolicyNumberValue = $this->getRowValue($row, [
            'insurance_policy_number', 'Insurance policy number', 'Insurance Policy Number',
        ]);

        $insuranceCardNumberValue = $this->getRowValue($row, [
            'insurance_card_number', 'Insurance card number', 'Insurance Card Number',
        ]);

        $insurancePolicyStartDate = $this->parseDate($this->getRowValue($row, [
            'insurance_effective', 'Insurance Effective', 'insurance_start_date', 'Insurance start date', 'Insurance Start Date',
        ]));

        $insurancePolicyEndDate = $this->parseDate($this->getRowValue($row, [
            'insurance_expiry', 'Insurance Expiry', 'insurance_end_date', 'Insurance end date', 'Insurance End Date',
        ]));


        // ─── Other Deductions ────────────────────────────────────────────
        $otherDeductionValue = $this->money(
            $row['other_deductions'] ?? $row['other_deduction'] ?? $row['Other Deductions'] ?? 0
        );

        // ─── Custom Fields ───────────────────────────────────────────────
        $customFields = [];

        if (empty($customFields['payroll_company'])) {
            $companyName = CompanySetting::query()->value('company_name');
            if (!empty($companyName)) {
                $customFields['payroll_company'] = $companyName;
            }
        }

        if ($otherDeductionValue > 0) {
            $customFields['Other Deductions'] = $otherDeductionValue;
        }

        // ─── Create Employee ─────────────────────────────────────────────
        $employeeIdFromExcel = $this->getRowValue($row, ['employee_id', 'Employee ID', 'employee id']);

        $employeeName = trim((string) ($this->getRowValue($row, ['employee_name', 'Employee Name']) ?? ''));
        $nameParts = preg_split('/\s+/', $employeeName, 2);
        $firstName = $nameParts[0] ?? ($row['first_name'] ?? null);
        $lastName = $nameParts[1] ?? ($row['last_name'] ?? '');

        // WPS ID
        $wpsId = $this->getRowValue($row, ['wps_id', 'WPS ID', 'wps personal number', 'wps_personal_number']);

        $companyResolved = $companyId;

        $employee = Employee::create([
            'employee_code'       => !empty($employeeIdFromExcel) ? (string) $employeeIdFromExcel : Employee::generateEmployeeCode(),
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'email'               => $row['email'] ?? null,
            'phone'               => $row['phone'] ?? null,
            'company_id'          => $companyResolved,
            'nationality'         => $nationality,
            'date_of_birth'       => $dateOfBirth,
            'country_id'          => Country::query()
                                        ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $nationality))])
                                        ->value('id'),
            'wps_personal_number' => $wpsId ?? null,
            'department_id'       => $departmentId,
            'designation_id'      => $designationId,
            'joining_date'        => $this->parseDate($row['joining_date'] ?? null) ?? now(),
            'status'              => $row['status'] ?? 'active',
            'bank_name'           => $row['bank_name'] ?? null,
            'bank_account_number' => $row['bank_account_number'] ?? null,
            'iban'                => $row['iban'] ?? null,
            'address'             => $row['address'] ?? null,

            // Insurance columns correctly mapped
            'insurance_provider'        => $insuranceProviderValue,
            'insurance_policy_number'   => !empty($insurancePolicyNumberValue) ? (string) $insurancePolicyNumberValue : null,
            'insurance_card_number'     => !empty($insuranceCardNumberValue)   ? (string) $insuranceCardNumberValue   : null,
            'insurance_start_date'      => $insurancePolicyStartDate,
            'insurance_end_date'        => $insurancePolicyEndDate,

            'custom_fields' => $customFields,
        ]);

        // ─── Salary Structure ────────────────────────────────────────────
        $employee->salaryStructures()->create([
            'basic_salary'              => $this->money($row['basic_salary'] ?? 0),
            'increment_value'           => $this->money($row['increment_value'] ?? 0),
            'overtime_rate_per_hour'    => $this->money($row['overtime_rate_per_hour'] ?? $row['overtime_rate'] ?? 0),
            'wps_first_transfer_amount' => $this->money($row['wps_first_transfer_amount'] ?? $row['wps_first_transfer_column'] ?? 0),
            'food_deduction'            => $this->money($row['food_deduction'] ?? 0),
            'visa_deduction'            => $this->money($row['visa_deduction'] ?? 0),
            'insurance_deduction'       => $this->money($row['insurance_deduction'] ?? $row['Insurance Deduction'] ?? $row['Insurance deduction'] ?? 0),
            'advance_payment'           => $this->money($row['advance_payment'] ?? 0),
            'is_active'                 => true,
            'effective_from'            => now(),
        ]);

        // ─── Documents ───────────────────────────────────────────────────

        // Passport
        $passportNumber    = $this->getRowValue($row, ['passport_number', 'Passport Number']);
        $passportExpiry    = $this->parseDate($this->getRowValue($row, ['passport_expiry', 'Passport Expiry', 'passport_expiry_date', 'Passport expiry date']));

        if (!empty($passportNumber) || !empty($passportExpiry)) {
            $employee->documents()->create([
                'document_type'   => 'passport',
                'document_number' => !empty($passportNumber) ? (string) $passportNumber : null,
                'issue_date'      => null,
                'expiry_date'     => $passportExpiry,
            ]);
        }

        // Emirates ID (mapped from VISA From / VISA Expiry as per requirement)
        $emiratesIdNumber = $this->getRowValue($row, ['visa_from', 'VISA From', 'emirates_id_number', 'Emirates ID Number']);
        $emiratesIdIssueDate = $this->parseDate($this->getRowValue($row, ['visa_from_issue_date', 'VISA From issue date']));
        if (empty($emiratesIdIssueDate)) {
            $emiratesIdIssueDate = $this->parseDate($this->getRowValue($row, ['visa_from', 'VISA From']));
        }

        $emiratesIdExpiry = $this->parseDate($this->getRowValue($row, ['visa_expiry', 'VISA Expiry', 'emirates_id_expiry_date', 'Emirates ID expiry date']));

        if (!empty($emiratesIdNumber) || !empty($emiratesIdExpiry) || !empty($emiratesIdIssueDate)) {
            $employee->documents()->create([
                'document_type'   => 'emirates_id',
                'document_number' => !empty($emiratesIdNumber) ? (string) $emiratesIdNumber : null,
                'issue_date'      => $emiratesIdIssueDate,
                'expiry_date'     => $emiratesIdExpiry,
            ]);
        }

        // ✅ Insurance as document_type='insurance' (issues + expiry both)
        if (!empty($insuranceProviderValue) || !empty($insurancePolicyNumberValue) || !empty($insurancePolicyStartDate) || !empty($insurancePolicyEndDate)) {
            $employee->documents()->updateOrCreate(
                ['document_type' => 'insurance', 'employee_id' => $employee->id],
                [
                    'document_number' => !empty($insurancePolicyNumberValue) ? (string) $insurancePolicyNumberValue : null,
                    'issue_date'      => $insurancePolicyStartDate,
                    'expiry_date'     => $insurancePolicyEndDate,
                    'file_path'       => null,
                    'notes'           => !empty($insuranceProviderValue) ? (string) $insuranceProviderValue : null,
                ]
            );
        }


        // ✅ Labour Card import removed (replaced by Insurance)

        // ✅ Driving License — pehle missing tha

        $drivingLicenseNumber    = $this->getRowValue($row, ['driving_license_number', 'Driving License Number', 'Driving license number']);
        $drivingLicenseIssueDate = $this->parseDate($this->getRowValue($row, ['driving_license_issue_date', 'Driving license issue date']));
        $drivingLicenseExpiry    = $this->parseDate($this->getRowValue($row, ['driving_license_expiry_date', 'Driving license expiry date']));

        if (!empty($drivingLicenseNumber) || !empty($drivingLicenseExpiry)) {
            $employee->documents()->create([
                'document_type'   => 'driving_license',
                'document_number' => !empty($drivingLicenseNumber) ? (string) $drivingLicenseNumber : null,
                'issue_date'      => $drivingLicenseIssueDate,
                'expiry_date'     => $drivingLicenseExpiry,
            ]);
        }

        // ─── Advance Payment ─────────────────────────────────────────────
        $advanceAmount = $this->money($row['advance_payment'] ?? $row['advance_amount'] ?? 0);

        if ($advanceAmount > 0) {
            $advanceDate       = $this->parseDate($row['advance_date'] ?? null) ?? now()->toDateString();
            $totalInstallments = (int) ($row['advance_total_installments'] ?? $row['advance_installments'] ?? 1);
            $installmentAmount = $this->money($row['advance_installment_amount'] ?? 0);
            $reason            = trim((string) ($row['advance_reason'] ?? ''));

            if ($installmentAmount <= 0 && $totalInstallments > 0) {
                $installmentAmount = round($advanceAmount / $totalInstallments, 2);
            }

            $advance = $employee->advances()->create([
                'amount'             => $advanceAmount,
                'advance_date'       => $advanceDate,
                'reason'             => $reason !== '' ? $reason : 'Advance Payment',
                'installment_amount' => $installmentAmount > 0 ? $installmentAmount : $advanceAmount,
                'total_installments' => $totalInstallments > 0 ? $totalInstallments : 1,
                'paid_installments'  => 0,
                'recovered_amount'   => 0,
                'pending_amount'     => $advanceAmount,
                'status'             => 'active',
            ]);

            ActivityLog::record('created', "Advance payment created for {$employee->full_name}", $advance);
        }

        // ─── Visa Installments ───────────────────────────────────────────
        $visaMonthly           = $this->money($row['visa_deduction'] ?? 0);
        $visaTotalInstallments = (int) ($row['total_installments'] ?? $row['visa_total_installments'] ?? 0);

        if ($visaTotalInstallments <= 0) $visaTotalInstallments = 1;

        if ($visaMonthly > 0) {
            $visaAdvance = $employee->advances()->create([
                'amount'             => $visaMonthly,
                'advance_date'       => now()->toDateString(),
                'reason'             => 'Visa Charges (Installments)',
                'installment_amount' => round($visaMonthly / $visaTotalInstallments, 2),
                'total_installments' => $visaTotalInstallments,
                'paid_installments'  => 0,
                'recovered_amount'   => 0,
                'pending_amount'     => $visaMonthly,
                'status'             => 'active',
            ]);

            ActivityLog::record('created', "Visa installment advance created for {$employee->full_name}", $visaAdvance);
        }

        ActivityLog::record('created', "Employee {$employee->full_name} created", $employee);

        return null;
    }

    // ✅ company_code bhi save hoga ab naye company ke saath
    private function resolveCompanyId($companyValue = null, $companyCode = null, $companyId = null): ?int
    {
        // Priority 1: company_id (numeric ID)
        if (!empty($companyId) && is_numeric($companyId)) {
            return Company::query()->whereKey((int) $companyId)->value('id');
        }

        // Priority 2: company_code se dhundo
        if (!empty($companyCode)) {
            $code = strtolower(trim((string) $companyCode));
            $found = Company::query()->whereRaw('LOWER(company_code) = ?', [$code])->value('id');
            if ($found) return $found;
        }

        // Priority 3: company_name se dhundo ya create karo
        $companyName = trim((string) ($companyValue ?? ''));

        if ($companyName === '') {
            // Koi bhi value nahi — default active company lo
            return Company::query()->where('is_active', true)->orderBy('company_name')->value('id');
        }

        // ✅ Company exist karti hai toh return karo, nahi toh company_code ke saath create karo
        $company = Company::query()->firstOrCreate(
            [
                'company_name' => $companyName,
            ],
            [
                'company_code' => !empty($companyCode) ? trim((string) $companyCode) : null,
                'is_active'    => true,
            ]
        );

        return $company->id;
    }

    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }

    private function normalizeHeaderKey(string $key): string
    {
        $key = str_replace('_', ' ', $key);
        return strtolower(trim(preg_replace('/\s+/', ' ', (string) $key) ?? ''));
    }

    private function getRowValue(array $row, array $possibleKeys)
    {
        $normalizedMap = [];
        foreach ($row as $key => $val) {
            if (!is_string($key)) continue;
            $normalizedMap[$this->normalizeHeaderKey($key)] = $val;
        }

        foreach ($possibleKeys as $possibleKey) {
            $nk = $this->normalizeHeaderKey($possibleKey);
            if (array_key_exists($nk, $normalizedMap)) {
                return $normalizedMap[$nk];
            }
        }

        return null;
    }

    private function parseDate($value): ?string
    {
        if ($value === null) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                $unixTimestamp = (int) round(((float) $value - 25569) * 86400);
                return Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        if (is_string($value) && trim($value) === '') return null;

        if (is_string($value) && str_contains($value, '/')) {
            try {
                return Carbon::createFromFormat('d/m/Y', trim($value))->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRowCount(): int { return $this->rowCount; }
    public function getErrors(): array { return $this->errors; }
}