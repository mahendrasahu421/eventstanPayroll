<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\PayrollRecord;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayrollAIController extends Controller
{
    public function explainSalarySlip(Request $request, GeminiService $gemini)
    {
        $payload = $request->validate([
            'record_id' => 'required|integer|exists:payroll_records,id',
            'question' => 'nullable|string|max:500',
        ]);

        $record = PayrollRecord::with(['employee.company', 'employee.department', 'employee.designation'])->findOrFail($payload['record_id']);
        $company = $record->employee?->company;
        $currency = $company?->currency_symbol ?? 'AED';

        $question = trim((string) ($payload['question'] ?? ''));
        if ($question === '') {
            $question = 'Explain this salary slip in simple terms. Include a brief summary of earnings, deductions, net pay, and note any key figures (attendance, overtime, and WPS splits). Do not invent values not present in the slip.';
        }

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
            'housing_allowance' => $record->housing_allowance,
            'transport_allowance' => $record->transport_allowance,
            'medical_allowance' => $record->medical_allowance,
            'other_allowance' => $record->other_allowance,
            'gross_salary' => $record->gross_salary,

            'food_deduction' => $record->food_deduction,
            'visa_deduction' => $record->visa_deduction,
            'insurance_deduction' => $record->insurance_deduction,
            'advance_deduction' => $record->advance_deduction,
            'other_deduction' => $record->other_deduction,
            'total_deductions' => $record->total_deductions,

            'net_salary' => $record->net_salary,
            'currency' => $currency,

            'wps_first_transfer' => $record->wps_first_transfer,
            'wps_second_transfer' => $record->wps_second_transfer,
            'status' => $record->status,
        ];

        $prompt = "You are an accounting assistant for a payroll system.\n\n".
            "User question: {$question}\n\n".
            "Salary slip data (JSON):\n".
            json_encode($payslipData, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

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
            Log::error('Gemini explainSalarySlip failed', [
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI explanation failed. Please check Gemini configuration and try again.',
            ], 500);
        }
    }
}

