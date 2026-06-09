<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\PayrollRecord;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardAIController extends Controller
{
    public function aiSummary(Request $request, GeminiService $gemini)
    {
        $payload = $request->validate([
            'question' => 'nullable|string|max:500',
        ]);

        $question = trim((string) ($payload['question'] ?? ''));
        if ($question === '') {
            $question = 'Summarize the current payroll dashboard status. Explain key KPIs (employees count, payroll processed vs pending, monthly net), document expiry risks, and payroll trend highlights. Keep it concise and actionable.';
        }

        $user = $request->user();
        $companyId = $user?->company_id;

        // Compute the same critical information used on the dashboard.
        $currentMonth = now()->format('Y-m');

        $stats = [
            'total_employees' => Employee::active()->count(),
            'payroll_processed' => PayrollRecord::forMonth($currentMonth)->count(),
            'payroll_pending' => Employee::active()->count() - PayrollRecord::forMonth($currentMonth)->whereIn('status', ['processed', 'approved', 'paid'])->count(),
            'monthly_net' => PayrollRecord::forMonth($currentMonth)->sum('net_salary'),
            'active_advances' => \App\Models\AdvancePayment::where('status', 'active')->sum('pending_amount'),
        ];

        // Expiring docs within 30 days
        $expiringDocs = \App\Models\EmployeeDocument::with('employee')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Trend last 6 months
        $trend = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i)->format('Y-m');
            return [
                'month' => now()->subMonths($i)->format('M Y'),
                'total_net' => PayrollRecord::forMonth($month)->sum('net_salary'),
            ];
        });

        // Upcoming holidays (table may not exist yet in some environments)
        try {
            $upcomingHolidays = Holiday::query()
                ->whereDate('date', '>=', now())
                ->whereDate('date', '<=', now()->addDays(60))
                ->orderBy('date')
                ->get();
        } catch (\Throwable $e) {
            $upcomingHolidays = collect();
        }

        $prompt = "You are an HR & Payroll assistant for a company payroll system.\n\n".
            "Company ID: {$companyId}\n\n".
            "Dashboard question: {$question}\n\n".
            "Dashboard KPI snapshot (raw numbers):\n".
            json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            "Document expiry alerts (next 30 days, up to 10 items):\n".
            json_encode($expiringDocs->map(function ($d) {
                return [
                    'employee' => $d->employee?->full_name,
                    'document_type' => $d->document_type,
                    'expiry_date' => $d->expiry_date?->format('Y-m-d'),
                    'days_left' => now()->diffInDays($d->expiry_date, false),
                ];
            }), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            "Payroll net trend (last 6 months):\n".
            json_encode($trend, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            "Upcoming holidays (next 60 days):\n".
            json_encode($upcomingHolidays->map(function ($h) {
                return [
                    'name' => $h->name,
                    'type' => $h->type,
                    'date' => $h->date?->format('Y-m-d'),
                ];
            }), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            "Respond in plain language with:\n".
            "1) 2-4 bullet summary\n".
            "2) any risks or urgent items\n".
            "3) recommended next actions\n";

        try {
            $answer = $gemini->generateText($prompt, [
                'temperature' => 0.2,
                'max_output_tokens' => 600,
            ]);

            return response()->json([
                'success' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini dashboard aiSummary failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI summary failed. Please check Gemini configuration and try again.',
            ], 500);
        }
    }

    public function explainLatestSlip(Request $request, GeminiService $gemini)
    {
        $payload = $request->validate([
            'question' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $companyId = $user?->company_id;

        $question = trim((string) ($payload['question'] ?? ''));
        if ($question === '') {
            $question = 'Explain the latest salary slip in simple terms. Summarize earnings, deductions, net pay, and highlight any key changes. Do not invent missing values.';
        }

        $record = PayrollRecord::query()
            ->with(['employee.company', 'employee.department', 'employee.designation'])
            ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)))
            ->orderByDesc('updated_at')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'No payroll record found to explain.',
            ], 404);
        }

        $company = $record->employee?->company;
        $currency = $company?->currency_symbol ?? 'AED';

        $payslipData = [
            'company_name' => $company?->company_name,
            'employee_name' => $record->employee?->full_name,
            'employee_code' => $record->employee?->employee_code,
            'department' => $record->employee?->department?->name,
            'designation' => $record->employee?->designation?->name,
            'payroll_month' => $record->payroll_month,
            'working_days' => $record->working_days,
            'present_days' => $record->present_days,
            'leave_days' => $record->leave_days,
            'overtime_hours' => $record->overtime_hours,
            'basic_salary' => $record->basic_salary,
            'gross_salary' => $record->gross_salary,
            'total_deductions' => $record->total_deductions,
            'net_salary' => $record->net_salary,
            'currency' => $currency,
            'status' => $record->status,
        ];

        $prompt = "You are an accounting assistant for a payroll system.\n\n".
            "User question: {$question}\n\n".
            "Latest salary slip data (JSON):\n".
            json_encode($payslipData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n\n".
            "Explain in simple terms. Mention key figures (gross, total deductions, net). If status is draft/pending, say so. Do not invent values.";

        try {
            $answer = $gemini->generateText($prompt, [
                'temperature' => 0.2,
                'max_output_tokens' => 600,
            ]);

            return response()->json([
                'success' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini dashboard explainLatestSlip failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI explanation failed. Please check Gemini configuration and try again.',
            ], 500);
        }
    }
}

