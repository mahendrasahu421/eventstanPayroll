<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\ActivityLog;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollExport;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function previewBreakdown(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'month'         => 'required|date_format:Y-m',
            'working_days'  => 'nullable|integer|min:1|max:31',
            'present_days'  => 'nullable|integer|min:0|max:31',
            'leave_days'    => 'nullable|integer|min:0',
            'overtime_hours'=> 'nullable|numeric|min:0',

            'food_deduction'      => 'nullable|numeric|min:0',
            'visa_deduction'      => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'advance_deduction'   => 'nullable|numeric|min:0',
            'other_deduction'     => 'nullable|numeric|min:0',

            'wps_first_transfer'  => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::with('salaryStructure')
            ->findOrFail($validated['employee_id']);

        $inputs = $validated;
        unset($inputs['employee_id'], $inputs['month']);

        $breakdown = $this->payrollService->calculateBreakdownPreview($employee, $validated['month'], $inputs);

        return response()->json(['success' => true, 'data' => $breakdown]);
    }


    // ── Process Single Payroll ─────────────────────────────────────────────

    public function processForm(Request $request)
    {
        $month     = $request->get('month', now()->format('Y-m'));
        $employees = Employee::active()->with('salaryStructure', 'department')->orderBy('first_name')->get();

        return view('payroll.process', compact('employees', 'month'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'month'         => 'required|date_format:Y-m',
            'working_days'  => 'nullable|integer|min:1|max:31',
            'present_days'  => 'nullable|integer|min:0|max:31',
            'leave_days'    => 'nullable|integer|min:0',
            'overtime_hours'=> 'nullable|numeric|min:0',

            // Deductions (overrides)
            'food_deduction'      => 'nullable|numeric|min:0',
            'visa_deduction'      => 'nullable|numeric|min:0',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'advance_deduction'   => 'nullable|numeric|min:0',
            'other_deduction'     => 'nullable|numeric|min:0',

            // WPS split
            'wps_first_transfer'  => 'nullable|numeric|min:0',

            // Save modes
            'save_status' => 'required|in:draft,paid',
        ]);

        $employee = Employee::with('salaryStructure')->findOrFail($validated['employee_id']);

        $validated['status'] = $validated['save_status'];
        unset($validated['save_status']);

        $record = $this->payrollService->processEmployee($employee, $validated['month'], $validated);

        ActivityLog::record(
            'payroll_processed',
            "Payroll {$validated['status']} for {$employee->full_name} ({$validated['month']})",
            $record
        );

        return redirect()->route('payroll.history')
            ->with('success', "Payroll {$validated['status']} for {$employee->full_name}.");

    }

    // ── Bulk Payroll ───────────────────────────────────────────────────────

    public function bulkForm()
    {
        return view('payroll.bulk');
    }

    public function bulkProcess(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);

        $results = $this->payrollService->bulkProcess($request->month, $request->exclude_ids ?? []);

        ActivityLog::record('bulk_payroll', "Bulk payroll processed for {$request->month}");

        return redirect()->route('payroll.history')
            ->with('bulk_results', $results)
            ->with('success', count($results['success']) . ' employees processed successfully.');
    }

    // ── Payroll History ────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $query = PayrollRecord::with(['employee.department'])
            ->when($request->month, fn($q) => $q->forMonth($request->month))
            ->when($request->employee, fn($q) => $q->where('employee_id', $request->employee))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->department, fn($q) => $q->whereHas('employee', fn($e) =>
                $e->where('department_id', $request->department)
            ));

        $records   = $query->orderBy('payroll_month', 'desc')->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('first_name')->get();

        return view('payroll.history', compact('records', 'employees'));
    }

    // ── Status Management ──────────────────────────────────────────────────

    public function updateStatus(Request $request, PayrollRecord $record)
    {
        $request->validate(['status' => 'required|in:draft,processed,approved,paid']);

        $old = $record->status;
        $updates = ['status' => $request->status];

        if ($request->status === 'approved') {
            $updates['approved_by'] = auth()->id();
            $updates['approved_at'] = now();
        }

        $record->update($updates);
        ActivityLog::record('status_changed', "Payroll status changed from {$old} to {$request->status}", $record);

        return back()->with('success', 'Status updated.');
    }

    // ── Salary Slip ────────────────────────────────────────────────────────

    public function salarySlip(PayrollRecord $record)
    {
        $record->load('employee.department', 'employee.designation');
        $company = \App\Models\CompanySetting::first();
        $pdf = Pdf::loadView('payroll.salary-slip', compact('record', 'company'));
        return $pdf->download("salary-slip-{$record->employee->employee_code}-{$record->payroll_month}.pdf");
    }

    // ── Reports ────────────────────────────────────────────────────────────

    public function reports(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $summary = $this->payrollService->getMonthlySummary($month);

        $departmentReport = PayrollRecord::with('employee.department')
            ->forMonth($month)
            ->get()
            ->groupBy(fn($r) => $r->employee?->department?->name ?? 'Unassigned')
            ->map(fn($group) => [
                'count'           => $group->count(),
                'total_gross'     => $group->sum('gross_salary'),
                'total_net'       => $group->sum('net_salary'),
                'total_deductions'=> $group->sum('total_deductions'),
            ]);

        return view('payroll.reports', compact('summary', 'departmentReport', 'month'));
    }

    public function exportExcel(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        return Excel::download(new PayrollExport($month), "payroll-{$month}.xlsx");
    }

    // ── WPS Report ─────────────────────────────────────────────────────────

    public function wpsReport(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $wpsData = $this->payrollService->generateWPSData($month);
        return view('payroll.wps', compact('wpsData', 'month'));
    }

    public function exportWPS(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $wpsData = $this->payrollService->generateWPSData($month);

        // Generate SIF-formatted WPS file
        $content = $this->generateSIFContent($wpsData, $month);

        return response($content, 200, [
            'Content-Type'        => 'text/plain',
            'Content-Disposition' => "attachment; filename=WPS_{$month}.sif",
        ]);
    }

    private function generateSIFContent(array $wpsData, string $month): string
    {
        $lines = [];
        $lines[] = "EH|{$month}|" . count($wpsData) . "|" . array_sum(array_column($wpsData, 'net_salary'));
        foreach ($wpsData as $row) {
            $lines[] = implode('|', [
                'ED', $row['wps_personal_number'], $row['iban'],
                number_format($row['net_salary'], 2, '.', ''),
            ]);
        }
        return implode("\r\n", $lines);
    }
}
