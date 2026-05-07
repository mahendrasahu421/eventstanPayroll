<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;

class EmployeesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    private int $rowCount = 0;
    private array $errors = [];

    public function model(array $row): ?Employee
    {
        // Skip empty rows
        if (empty(trim($row['first_name'] ?? ''))) return null;

        // Check duplicate
        if (!empty($row['email']) && Employee::where('email', $row['email'])->exists()) {
            $this->errors[] = "Row skipped: Email {$row['email']} already exists.";
            return null;
        }

        $departmentId  = null;
        $designationId = null;

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

        return new Employee([
            'employee_code'   => Employee::generateEmployeeCode(),
            'first_name'      => $row['first_name'],
            'last_name'       => $row['last_name'] ?? '',
            'email'           => $row['email'] ?? null,
            'phone'           => $row['phone'] ?? null,
            'nationality'     => $row['nationality'] ?? null,
            // country_id (if nationality text matches a master country name)
            'country_id'      => \App\Models\Country::query()
                ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) ($row['nationality'] ?? '')))])
                ->value('id'),
            'department_id'   => $departmentId,
            'designation_id'  => $designationId,
            'joining_date'    => $this->parseDate($row['joining_date'] ?? null) ?? now(),
            'status'          => 'active',
        ]);
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getRowCount(): int   { return $this->rowCount; }
    public function getErrors(): array   { return $this->errors; }
}
