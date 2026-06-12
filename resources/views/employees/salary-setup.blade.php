@extends('layouts.app')

@section('title', 'Salary Setup - ' . $employee->full_name)

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-currency-dollar fs-2 text-success"></i>
    <div>
        <h2>Salary Structure</h2>
        <p class="text-muted mb-0">{{ $employee->full_name }} ({{ $employee->employee_code }})</p>
    </div>
</div>

<form method="POST" action="{{ route('employees.salary.save', $employee) }}">
    @csrf

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Basic Salary *</label>
            <input type="number" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror"
                value="{{ old('basic_salary', $salary?->basic_salary ?? '') }}" step="0.01" min="0" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Housing Allowance</label>
            <input type="number" name="housing_allowance" class="form-control"
                value="{{ old('housing_allowance', $salary?->housing_allowance ?? '') }}" step="0.01" min="0">
        </div>

        <div class="col-md-6">
            <label class="form-label">Transport Allowance</label>
            <input type="number" name="transport_allowance" class="form-control"
                value="{{ old('transport_allowance', $salary?->transport_allowance ?? '') }}" step="0.01" min="0">
        </div>
        <div class="col-md-6">
            <label class="form-label">Medical Allowance</label>
            <input type="number" name="medical_allowance" class="form-control"
                value="{{ old('medical_allowance', $salary?->medical_allowance ?? '') }}" step="0.01" min="0">
        </div>

        <div class="col-md-6">
            <label class="form-label">Other Allowance</label>
            <input type="number" name="other_allowance" class="form-control"
                value="{{ old('other_allowance', $salary?->other_allowance ?? '') }}" step="0.01" min="0">
        </div>
        <div class="col-md-6">
            <label class="form-label">Overtime Rate (per hour)</label>
            <input type="number" name="overtime_rate_per_hour" class="form-control"
                value="{{ old('overtime_rate_per_hour', $salary?->overtime_rate_per_hour ?? '') }}" step="0.01" min="0">
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Deductions</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Food Deduction</label>
                    <input type="number" name="food_deduction" class="form-control"
                        value="{{ old('food_deduction', $salary?->food_deduction ?? '') }}" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Visa Total Charges</label>
                    <input type="number" name="visa_deduction" id="visa_total" class="form-control"
                        value="{{ old('visa_deduction', $salary?->visa_deduction ?? 0) }}" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Visa Installments (Months)</label>
                    @php
                        $activeVisa = $employee->advances
                            ->where('reason', 'Visa Charges (Installments)')
                            ->where('status', 'active')
                            ->first();
                        $existingMonths = $activeVisa ? $activeVisa->total_installments : 1;
                    @endphp
                    <input type="number" name="visa_total_installments" id="visa_months" class="form-control"
                        value="{{ old('visa_total_installments', $existingMonths) }}" min="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Monthly Recovery</label>
                    <div id="monthly_recovery_display" class="form-control bg-light fw-bold text-primary">
                        0.00
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Insurance Deduction</label>
                    <input type="number" name="insurance_deduction" class="form-control"
                        value="{{ old('insurance_deduction', $salary?->insurance_deduction ?? '') }}" step="0.01" min="0">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold">WPS First Transfer *</label>
            <input type="number" name="wps_first_transfer_amount"
                class="form-control @error('wps_first_transfer_amount') is-invalid @enderror"
                value="{{ old('wps_first_transfer_amount', $salary?->wps_first_transfer_amount ?? '') }}"
                step="0.01" min="0" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Effective From *</label>
            <input type="date" name="effective_from"
                class="form-control @error('effective_from') is-invalid @enderror"
                value="{{ old('effective_from', $salary?->effective_from ?? '') }}" required>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Salary Structure</button>
    </div>
</form>

@push('styles')
<style>
    .photo-upload {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const visaTotalInput = document.getElementById('visa_total');
    const visaMonthsInput = document.getElementById('visa_months');
    const display = document.getElementById('monthly_recovery_display');

    function updateRecovery() {
        const total = parseFloat(visaTotalInput.value) || 0;
        const months = parseInt(visaMonthsInput.value) || 1;
        display.innerText = (total / months).toFixed(2) + ' AED';
    }

    visaTotalInput.addEventListener('input', updateRecovery);
    visaMonthsInput.addEventListener('input', updateRecovery);
    updateRecovery(); // Initial calculation
});
</script>
@endpush

@endsection