<?php

namespace App\Exports;

use App\Models\PayrollRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private string $month) {}

    public function collection()
    {
        return PayrollRecord::with(['employee.department'])
            ->forMonth($this->month)
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Employee Code', 'Employee Name', 'Department',
            'Working Days', 'Present Days', 'Leave Days', 'OT Hours',
            'Basic Salary', 'Housing', 'Transport', 'Medical', 'Other Allowance',
            'Overtime Amount', 'Gross Salary',
            'Food Ded.', 'Visa Ded.', 'Insurance Ded.', 'Advance Ded.', 'Other Ded.',
            'Total Deductions', 'Net Salary',
            'WPS 1st Transfer', 'WPS 2nd Transfer',
            'Status',
        ];
    }

    public function map($record): array
    {
        return [
            $record->employee->employee_code,
            $record->employee->full_name,
            $record->employee->department?->name ?? 'N/A',
            $record->working_days,
            $record->present_days,
            $record->leave_days,
            $record->overtime_hours,
            $record->basic_salary,
            $record->housing_allowance,
            $record->transport_allowance,
            $record->medical_allowance,
            $record->other_allowance,
            $record->overtime_amount,
            $record->gross_salary,
            $record->food_deduction,
            $record->visa_deduction,
            $record->insurance_deduction,
            $record->advance_deduction,
            $record->other_deduction,
            $record->total_deductions,
            $record->net_salary,
            $record->wps_first_transfer,
            $record->wps_second_transfer,
            ucfirst($record->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2B5797'],
            ]],
        ];
    }
}
