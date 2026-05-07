<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->canManageEmployees();
    }

    public function rules(): array
    {
        $id = $this->route('employee')?->id;

        return [
            // Personal fields
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'email'              => "nullable|email|unique:employees,email,{$id}",
            'phone'              => 'nullable|string|max:20',
            'country_id'         => 'nullable|exists:countries,id',
            'nationality'        => 'nullable|string|max:50',

            // Optional backward compatibility: allow nationality text, but UI should use country_id

            'wps_personal_number'=> 'nullable|string|max:50',
            'joining_date'       => 'required|date',
            'department_id'      => 'required|exists:departments,id',
            'designation_id'     => 'required|exists:designations,id',
            'status'             => 'nullable|in:active,inactive',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Salary & Compensation
            'basic_salary'             => 'required|numeric|min:0',
            'increment_value'          => 'nullable|numeric|min:0',
            'overtime_rate_per_hour'   => 'nullable|numeric|min:0',
            'wps_first_transfer_amount'=> 'nullable|numeric|min:0',

            // Deductions
            'food_deduction'       => 'nullable|numeric|min:0',
            'visa_deduction'       => 'nullable|numeric|min:0',
            'insurance_deduction'  => 'nullable|numeric|min:0',
            'advance_payment'      => 'nullable|numeric|min:0',
            'other_deduction'      => 'nullable|numeric|min:0',

            // Documents (nested arrays)
            'documents' => 'nullable|array',
            'documents.passport' => 'nullable|array',
            'documents.passport.number' => 'nullable|string|max:50',
            'documents.passport.expiry_date' => 'nullable|date',
            'documents.passport.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'documents.emirates_id' => 'nullable|array',
            'documents.emirates_id.number' => 'nullable|string|max:50',
            'documents.emirates_id.expiry_date' => 'nullable|date',
            'documents.emirates_id.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'documents.labour_card' => 'nullable|array',
            'documents.labour_card.number' => 'nullable|string|max:50',
            'documents.labour_card.expiry_date' => 'nullable|date',
            'documents.labour_card.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'documents.driving_license' => 'nullable|array',
            'documents.driving_license.number' => 'nullable|string|max:50',
            'documents.driving_license.expiry_date' => 'nullable|date',
            'documents.driving_license.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // Custom Fields
            'custom_fields' => 'nullable|array',
            'custom_fields.*' => 'nullable|string|max:255',
            'dynamic_custom_fields' => 'nullable|array',
            'dynamic_custom_fields.*.name' => 'required|string|max:100',
            'dynamic_custom_fields.*.value' => 'nullable|string|max:255',
        ];
    }
}
