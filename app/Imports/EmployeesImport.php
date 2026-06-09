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
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class EmployeesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    private int $rowCount = 0;
    private array $errors = [];

    public function model(array $row): ?Employee
    {
       
        if (empty(trim($row['first_name'] ?? ''))) return null;

        // Check duplicate (manual create usually depends on unique fields; email is most common)
        if (!empty($row['email']) && Employee::where('email', $row['email'])->exists()) {
            $this->errors[] = "Row skipped: Email {$row['email']} already exists.";
            return null;
        }

        $departmentId  = null;
        $designationId = null;

        $companyId = $this->resolveCompanyId(
            $row['company']
                ?? $row['company_code']
                ?? $row['company_id']
                ?? null
        );

        $nationality = $row['nationality'] ?? null;

        $dateOfBirth = $this->parseDate($row['date_of_birth'] ?? null);


        if (!empty($row['department'])) {
            $dept = Department::firstOrCreate(['name' => trim($row['department'])], ['is_active' => true]);
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

        $customFields = [];

        // Mirror manual create behavior
        if (!empty($row['insurance_provider'] ?? null)) {
            $customFields['insurance_provider'] = $row['insurance_provider'];
        }

        if (empty($customFields['payroll_company'])) {
            $companyName = CompanySetting::query()->value('company_name');
            if (!empty($companyName)) {
                $customFields['payroll_company'] = $companyName;
            }
        }

        $employee = Employee::create([
            'employee_code'   => Employee::generateEmployeeCode(),
            'first_name'      => $row['first_name'],
            'last_name'       => $row['last_name'] ?? '',
            'email'           => $row['email'] ?? null,
            'phone'           => $row['phone'] ?? null,
            'company_id'      => $companyId,
            'nationality'     => $nationality,
            'date_of_birth'   => $dateOfBirth,
            'country_id'      => Country::query()
                ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $nationality))])
                ->value('id'),

            'wps_personal_number' => $row['wps_personal_number'] ?? null,
            'department_id'   => $departmentId,
            'designation_id'  => $designationId,
            
            // Passport/Emirates/Insurance data will be imported into employee_documents below.

            'joining_date'    => $this->parseDate($row['joining_date'] ?? null) ?? now(),
            'status'          => $row['status'] ?? 'active',
            'bank_name'       => $row['bank_name'] ?? null,
            'bank_account_number' => $row['bank_account_number'] ?? null,
            'iban'            => $row['iban'] ?? null,
            'address'         => $row['address'] ?? null,
            'custom_fields'   => $customFields,
        ]);


        $employee->salaryStructures()->create([
            'basic_salary' => $this->money($row['basic_salary'] ?? 0),
            'increment_value' => $this->money($row['increment_value'] ?? 0),
            'overtime_rate_per_hour' => $this->money($row['overtime_rate_per_hour'] ?? $row['overtime_rate'] ?? 0),
            'wps_first_transfer_amount' => $this->money($row['wps_first_transfer_amount'] ?? $row['wps_first_transfer_column'] ?? 0),

            'food_deduction' => $this->money($row['food_deduction'] ?? 0),
            'visa_deduction' => $this->money($row['visa_deduction'] ?? 0),
            'insurance_deduction' => $this->money($row['insurance_deduction'] ?? 0),
            'advance_payment' => $this->money($row['advance_payment'] ?? 0),
            // 'other_deduction' (payroll_records) is not stored in salary_structures in this codebase.

            'is_active' => true,
            'effective_from' => now(),
        ]);

        // Employee Documents import (Passport / Emirates ID / Insurance)
        $passportNumber = $row['Passport Number'] ?? ($row['passport_number'] ?? null);
        $passportExpiry = $this->parseDate($row['Passport expiry date'] ?? ($row['passport_expiry_date'] ?? null));

        if (!empty($passportNumber) || !empty($passportExpiry)) {
            $employee->documents()->create([
                'document_type' => 'passport',
                'document_number' => !empty($passportNumber) ? (string) $passportNumber : null,
                'expiry_date' => $passportExpiry,
                'issue_date' => null,
            ]);
        }

        $emiratesIdNumber = $this->getRowValue($row, [
            'Emirates ID Number',
            'Emirated ID Number',
            'emirates_id_number',
        ]);

        $emiratesIdExpiryRaw = $this->getRowValue($row, [
            'Emirated ID expiry date',
            'Emirates ID expiry date',
            'emirated id expiry date',
            'emirates id expiry date',
            'emirates_id_expiry_date',
        ]);

        $emiratesIdExpiry = $this->parseDate($emiratesIdExpiryRaw);


        if (!empty($emiratesIdNumber) || !empty($emiratesIdExpiry)) {
            $employee->documents()->create([
                'document_type' => 'emirates_id',
                'document_number' => !empty($emiratesIdNumber) ? (string) $emiratesIdNumber : null,
                'expiry_date' => $emiratesIdExpiry,
                'issue_date' => null,
            ]);
        }

        // Insurance policy/card
        $insurancePolicyNumber = $row['Insurance policy number'] ?? ($row['insurance_policy_number'] ?? null);
        $insuranceCardNumber = $row['Insurance card number'] ?? ($row['insurance_card_number'] ?? null);
        $insuranceStart = $this->parseDate($row['Insurance start date'] ?? ($row['insurance_start_date'] ?? null));
        $insuranceEnd = $this->parseDate($row['Insurance End date'] ?? ($row['insurance_end_date'] ?? null));

        if (!empty($insurancePolicyNumber) || !empty($insuranceCardNumber) || !empty($insuranceEnd)) {
            // store both numbers into notes, document_number as policy number (or card if policy missing)
            $employee->documents()->create([
                'document_type' => 'insurance',
                'document_number' => !empty($insurancePolicyNumber) ? (string) $insurancePolicyNumber : (!empty($insuranceCardNumber) ? (string) $insuranceCardNumber : null),
                'issue_date' => $insuranceStart,
                'expiry_date' => $insuranceEnd,
                'notes' => trim(
                    'policy: ' . ($insurancePolicyNumber ?? '-') .
                    '; card: ' . ($insuranceCardNumber ?? '-')
                ) ?: null,
            ]);
        }

        // Other deductions -> store in custom_fields
        $otherDeductions = $this->money($row['Other Deductions'] ?? ($row['other_deductions'] ?? 0));
        if ($otherDeductions > 0) {
            $customFields = is_array($employee->custom_fields) ? $employee->custom_fields : [];
            $customFields['other_deductions'] = $otherDeductions;
            $employee->update(['custom_fields' => $customFields]);
        }



        // NOTE: Visa deduction ko advance ki tarah convert nahi karna.
        // Excel me visa_deduction salary structure ke through fixed deduction rahega.

        // If Excel provides a visa total installments column, it's used below to create visa installment advances.


        // Create advance payments like manual create (bulk columns)
        // 1) Normal advance_payment (non-visa)
        $advanceAmount = $this->money($row['advance_payment'] ?? $row['advance_amount'] ?? 0);

        if ($advanceAmount > 0) {
            $advanceDate = $this->parseDate($row['advance_date'] ?? null) ?? now()->toDateString();
            $installmentAmount = $this->money($row['advance_installment_amount'] ?? 0);
            $totalInstallments = (int) ($row['advance_total_installments'] ?? $row['advance_installments'] ?? 1);
            $reason = trim((string) ($row['advance_reason'] ?? ''));

            // If installment amount missing but total installments present, distribute
            if ($installmentAmount <= 0 && $totalInstallments > 0) {
                $installmentAmount = round($advanceAmount / $totalInstallments, 2);
            }

            $advance = $employee->advances()->create([
                'amount' => $advanceAmount,
                'advance_date' => $advanceDate,
                'reason' => $reason !== '' ? $reason : 'Advance Payment',
                'installment_amount' => $installmentAmount > 0 ? $installmentAmount : $advanceAmount,
                'total_installments' => $totalInstallments > 0 ? $totalInstallments : 1,
                'paid_installments' => 0,
                'recovered_amount' => 0,
                'pending_amount' => $advanceAmount,
                'status' => 'active',
            ]);

            ActivityLog::record('created', "Advance payment created for {$employee->full_name}", $advance);
        }

        // 2) Visa installments - separate from employee advance
        // Excel headers used ONLY here:
        // - visa_deduction (monthly amount)
        // - total_installments (number of months)
        $visaMonthly = $this->money($row['visa_deduction'] ?? ($row['visa deduction'] ?? 0));

        // User sample uses `total_installments` for visa months.
        // Existing templates sometimes use `visa_total_installments`.
        $visaTotalInstallmentsRaw = $row['total_installments']
            ?? ($row['visa_total_installments'] ?? $row['visa total installments'] ?? ($row['total installments'] ?? 0));

        $visaTotalInstallments = (int) (is_null($visaTotalInstallmentsRaw) || $visaTotalInstallmentsRaw === '' ? 0 : $visaTotalInstallmentsRaw);
        if ($visaTotalInstallments <= 0) {
            $visaTotalInstallments = 1;
        }

        if ($visaMonthly > 0) {
            $visaTotalAmount = round($visaTotalInstallments, 2);

            $visaAdvance = $employee->advances()->create([
                'amount' => $visaTotalAmount,
                'advance_date' => now()->toDateString(),
                // payroll will treat this as visa recovery installments
                'reason' => 'Visa Charges (Installments)',
                'installment_amount' => ($visaMonthly/$visaTotalInstallments),
                'total_installments' => $visaTotalInstallments,
                'paid_installments' => 0,
                'recovered_amount' => 0,
                'pending_amount' => $visaTotalAmount,
                'status' => 'active',
            ]);

            ActivityLog::record('created', "Visa installment advance created for {$employee->full_name}", $visaAdvance);
        }




        ActivityLog::record('created', "Employee {$employee->full_name} created", $employee);


        return null;

    }

    private function resolveCompanyId($value): ?int
    {
        if (empty($value)) {
            return Company::query()->where('is_active', true)->orderBy('company_name')->value('id');
        }

        // If excel has company_id
        if (is_numeric($value)) {
            return Company::query()->whereKey((int) $value)->value('id');
        }

        // If excel has company_name, create company in master if not exists
        $companyName = trim((string) $value);
        if ($companyName === '') {
            return null;
        }

        $company = Company::query()->firstOrCreate(
            [
                // Case-insensitive lookup via normalized name
                // (we store the trimmed/original name in company_name)
                'company_name' => $companyName,
            ],
            [
                'is_active' => true,
            ]
        );

        // If there is an existing row with different casing/spaces, ensure we still return an id
        // by re-querying case-insensitively when firstOrCreate created a duplicate.
        if ($company && $company->wasRecentlyCreated === false) {
            return $company->id;
        }

        // Fallback: case-insensitive match
        return Company::query()
            ->whereRaw('LOWER(company_name) = ?', [strtolower($companyName)])
            ->value('id');
    }


    private function money($value): float
    {
        return (float) str_replace(',', '', (string) ($value ?? 0));
    }

    private function normalizeHeaderKey(string $key): string
    {
        // Excel headers can have trailing spaces, different casing, etc.
        return strtolower(trim(preg_replace('/\s+/', ' ', $key) ?? ''));
    }

    private function getRowValue(array $row, array $possibleKeys)
    {
        // WithHeadingRow uses the header text as keys.
        // But in Excel users might have small spelling/spacing variations.
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

        // WithHeadingRow can pass values like float/int (Excel date serial)
        // or already formatted strings.
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                // Excel serial date to timestamp.
                // Excel day 0 is 1899-12-31; Unix epoch assumptions can vary with the 1900 leap year bug.
                // This common conversion works for most spreadsheets.
                $excelDays = (float) $value;
                $unixTimestamp = (int) round(($excelDays - 25569) * 86400); // 25569 = days between 1970-01-01 and 1899-12-31
                return Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d');
            } catch (\Exception $e) {
                // fall through
            }
        }

        if (is_string($value) && trim($value) === '') return null;

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }


    public function getRowCount(): int   { return $this->rowCount; }
    public function getErrors(): array   { return $this->errors; }
}
