<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WPSExportController extends Controller
{
    /**
     * Build WPS SIF rows for the given month.
     *
     * NOTE: This creates a simple CSV-like payload that can be used by exportWPS.
     */
    public function buildWpsRows(string $month): array
    {
        $records = PayrollRecord::with(['employee.company'])
            ->where('payroll_month', $month)
            ->whereIn('status', ['paid', 'processed', 'approved'])
            ->get();

        $rows = [];

        foreach ($records as $record) {
            $employee = $record->employee;
            if (! $employee) {
                continue;
            }

            $iban = (string) ($employee->iban ?? '');
            if ($iban === '') {
                continue;
            }

            $rows[] = [
                'wps_personal_number' => (string) ($employee->wps_personal_number ?? $employee->employee_code ?? ''),
                'employee_name' => (string) ($employee->full_name ?? trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''))),
                'iban' => $iban,
                'net_salary' => (float) ($record->net_salary ?? 0),
            ];
        }

        // Ensure stable order
        usort($rows, function ($a, $b) {
            return strcmp((string) $a['wps_personal_number'], (string) $b['wps_personal_number']);
        });

        return $rows;
    }

    /**
     * Export as a simple SIF-like text file.
     */
    public function exportWps(string $month)
    {
        try {
            $rows = $this->buildWpsRows($month);

            $filename = 'WPS_SIF_' . Str::replace('-', '', $month) . '.txt';

            $lines = [];
            foreach ($rows as $row) {
                // Format: personalNumber,IBAN,amount
                $lines[] = implode(',', [
                    preg_replace('/\s+/', '', $row['wps_personal_number']),
                    preg_replace('/\s+/', '', $row['iban']),
                    number_format((float) $row['net_salary'], 2, '.', ''),
                ]);
            }

            $content = implode(PHP_EOL, $lines);

            return response($content, 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Throwable $e) {
            Log::error('WPS export failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            abort(500, 'WPS export failed: ' . $e->getMessage());
        }
    }
}

