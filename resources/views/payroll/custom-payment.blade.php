@extends('layouts.app')

@section('title', 'Custom Salary Payment')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-info bg-opacity-10 p-3">
                <i class="bi bi-wallet2 fs-2 text-info"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Custom Salary Payment</h1>
                <p class="text-muted mb-0">Generate month-wise, multi-month, or custom date-range salary payments</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('payroll.process') }}" class="btn btn-outline-primary">
                <i class="bi bi-calculator me-1"></i> Single Payroll
            </a>
            <a href="{{ route('payroll.history') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i> Payroll History
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('payroll.custom-payment.store') }}" id="customPaymentForm">
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-sliders me-2 text-info"></i>Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                            {{ $employee->full_name }} ({{ $employee->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Payment Type <span class="text-danger">*</span></label>
                                <select name="payment_type" id="paymentType" class="form-select @error('payment_type') is-invalid @enderror" required>
                                    <option value="full" @selected(old('payment_type', 'full') === 'full')>Full Salary</option>
                                    <option value="partial" @selected(old('payment_type') === 'partial')>Partial Salary</option>
                                    <option value="hold" @selected(old('payment_type') === 'hold')>Hold Salary</option>
                                    <option value="release" @selected(old('payment_type') === 'release')>Release Hold</option>
                                </select>
                                @error('payment_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Scope <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_scope" id="scopeMonth" value="month" @checked(old('payment_scope', 'month') === 'month')>
                                        <label class="form-check-label" for="scopeMonth">Month-wise salary</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_scope" id="scopeRange" value="range" @checked(old('payment_scope') === 'range')>
                                        <label class="form-check-label" for="scopeRange">Custom date range</label>
                                    </div>
                                </div>
                                @error('payment_scope')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="monthScopeBox">
                                <label class="form-label fw-semibold">Primary Month</label>
                                <input type="month" name="month" class="form-control @error('month') is-invalid @enderror" value="{{ old('month', $month ?? now()->format('Y-m')) }}">
                                @error('month')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12" id="multiMonthBox">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-semibold mb-0">Additional Months</label>
                                    <small class="text-muted">Leave blank if not required</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="month" name="months[]" class="form-control" value="{{ old('months.0') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="month" name="months[]" class="form-control" value="{{ old('months.1') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="month" name="months[]" class="form-control" value="{{ old('months.2') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 d-none" id="startDateBox">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 d-none" id="endDateBox">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-info"></i>Adjustment Controls</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Present Days</label>
                                <input type="number" name="present_days" class="form-control" min="0" step="1" value="{{ old('present_days') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Leave Days</label>
                                <input type="number" name="leave_days" class="form-control" min="0" step="1" value="{{ old('leave_days') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Overtime Hours</label>
                                <input type="number" name="overtime_hours" class="form-control" min="0" step="0.5" value="{{ old('overtime_hours') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Food Deduction</label>
                                <input type="number" name="food_deduction" class="form-control" min="0" step="0.01" value="{{ old('food_deduction') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visa Deduction</label>
                                <input type="number" name="visa_deduction" class="form-control" min="0" step="0.01" value="{{ old('visa_deduction') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Insurance Deduction</label>
                                <input type="number" name="insurance_deduction" class="form-control" min="0" step="0.01" value="{{ old('insurance_deduction') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Advance Adjustment</label>
                                <input type="number" name="advance_deduction" class="form-control" min="0" step="0.01" value="{{ old('advance_deduction') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Other Deduction</label>
                                <input type="number" name="other_deduction" class="form-control" min="0" step="0.01" value="{{ old('other_deduction') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WPS First Transfer</label>
                                <input type="number" name="wps_first_transfer" class="form-control" min="0" step="0.01" value="{{ old('wps_first_transfer') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Partial Amount</label>
                                <input type="number" name="partial_amount" class="form-control" min="0" step="0.01" value="{{ old('partial_amount') }}">
                                <small class="text-muted">Used for partial salary payment tracking.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="4" class="form-control" placeholder="Optional notes, approvals, or hold/release comments">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 flex-wrap">
                    <a href="{{ route('payroll.history') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Custom Payment
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>How it works</h5>
                </div>
                <div class="card-body">
                    <ul class="text-muted mb-0">
                        <li>Month-wise payment supports one or more months in a single transaction.</li>
                        <li>Date-range payment calculates salary based on the selected start and end dates.</li>
                        <li>Use Hold to save a draft-like payment and Release to mark a held salary as payable.</li>
                        <li>Partial amount is stored for tracking partial salary settlements.</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-info"></i>Payroll Notes</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        The final payroll record is saved into the same payroll history table, so reports and salary slips remain consistent.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scopeMonth = document.getElementById('scopeMonth');
        const scopeRange = document.getElementById('scopeRange');
        const monthScopeBox = document.getElementById('monthScopeBox');
        const multiMonthBox = document.getElementById('multiMonthBox');
        const startDateBox = document.getElementById('startDateBox');
        const endDateBox = document.getElementById('endDateBox');
        const paymentType = document.getElementById('paymentType');

        function updateScopeUI() {
            const isRange = scopeRange.checked;
            monthScopeBox.classList.toggle('d-none', isRange);
            multiMonthBox.classList.toggle('d-none', isRange);
            startDateBox.classList.toggle('d-none', !isRange);
            endDateBox.classList.toggle('d-none', !isRange);
        }

        scopeMonth.addEventListener('change', updateScopeUI);
        scopeRange.addEventListener('change', updateScopeUI);

        paymentType.addEventListener('change', function () {
            if (this.value === 'hold') {
                this.classList.add('border-warning');
            } else {
                this.classList.remove('border-warning');
            }
        });

        updateScopeUI();
    });
</script>
@endpush
@endsection
