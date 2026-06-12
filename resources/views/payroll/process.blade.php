@extends('layouts.app')

@section('title', 'Process Payroll')

@section('content')
    <div class="container-fluid px-4">
        {{-- Header Section --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-calculator fs-2 text-primary"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Process Payroll</h1>
                    <p class="text-muted mb-0">Calculate and manage employee salary</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payroll.history') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-clock-history me-1"></i> Payroll History
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left Column - Form Inputs --}}
            <div class="col-lg-7">
                <form method="POST" action="{{ route('payroll.calculate') }}" id="payrollForm">
                    @csrf
                    <input type="hidden" name="save_status" id="saveStatusInput" value="draft">

                    {{-- Payroll Inputs Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-sliders2 me-2 text-primary"></i>Payroll Inputs</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar-month"></i></span>
                                        <input type="month" name="month" id="monthInput"
                                            class="form-control @error('month') is-invalid @enderror"
                                            value="{{ old('month', $month ?? now()->format('Y-m')) }}" required>
                                    </div>
                                    @error('month')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" id="employeeSelect"
                                        class="form-select @error('employee_id') is-invalid @enderror" required>
                                        <option value="">-- Select Employee --</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}"
                                                data-code="{{ $emp->employee_code }}"
                                                data-joining="{{ $emp->joining_date }}"
                                                data-department="{{ $emp->department?->name }}"
                                                data-designation="{{ $emp->designation?->name }}"
                                                data-company="{{ $emp->company?->company_name }}">
                                                {{ $emp->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4" />

                            {{-- Attendance Section --}}
                            <h6 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2 text-primary"></i>Attendance</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Working Days</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                                        <input type="number" id="workingDaysInput" name="working_days"
                                            value="30" min="1" max="31" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Present Days <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-check-circle"></i></span>
                                        <input type="number" id="presentDaysInput" name="present_days"
                                            class="form-control @error('present_days') is-invalid @enderror"
                                            value="{{ old('present_days', 30) }}" min="0" max="31" required>
                                    </div>
                                    @error('present_days')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Overtime Hours</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-clock-history"></i></span>
                                        <input type="number" id="overtimeHoursInput" name="overtime_hours"
                                            class="form-control" value="{{ old('overtime_hours', 0) }}"
                                            step="0.5" min="0">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" />

                            {{-- Deductions Section --}}
                            <h6 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Deductions</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Food/Mess</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="foodDeductionInput" name="food_deduction"
                                            class="form-control" value="{{ old('food_deduction', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Visa</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="visaDeductionInput" name="visa_deduction"
                                            class="form-control" value="{{ old('visa_deduction', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Insurance</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="insuranceDeductionInput" name="insurance_deduction"
                                            class="form-control" value="{{ old('insurance_deduction', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Advance</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="advanceDeductionInput" name="advance_deduction"
                                            class="form-control" value="{{ old('advance_deduction', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                    <small class="text-muted mt-1 d-block" id="advanceHint">Loading...</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Other Deductions</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="otherDeductionInput" name="other_deduction"
                                            class="form-control" value="{{ old('other_deduction', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">WPS First Transfer</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">AED</span>
                                        <input type="number" id="wpsFirstTransferInput" name="wps_first_transfer"
                                            class="form-control" value="{{ old('wps_first_transfer', 0) }}"
                                            step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Bulk Processing Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-grid-3x3-gap-fill fs-4 text-primary me-2"></i>
                            <span class="fw-semibold">Process payroll for multiple employees</span>
                        </div>
                        <a href="{{ route('payroll.bulk') }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-1"></i> Bulk Payroll
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right Column - Salary Breakdown --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>Salary Breakdown
                        </h5>
                        <div id="breakdownActions" class="d-none">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="saveDraftBtn">
                                <i class="bi bi-save me-1"></i> Draft
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="savePaidBtn">
                                <i class="bi bi-check-circle me-1"></i> Paid
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        {{-- Employee Info Card --}}
                        <div id="employeeInfoCard" class="d-none"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin: 12px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0 fw-bold" id="empName">-</h6>
                                    <small id="empCode">-</small>
                                </div>
                                <i class="bi bi-person-badge fs-3 opacity-50"></i>
                            </div>
                            <div class="mt-2 mb-2">
                                <small class="opacity-75">Company</small><br>
                                <small class="fw-semibold" id="empCompany">-</small>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="opacity-75">Department</small><br>
                                    <small class="fw-semibold" id="empDept">-</small>
                                </div>
                                <div class="col-6">
                                    <small class="opacity-75">Designation</small><br>
                                    <small class="fw-semibold" id="empDesig">-</small>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="opacity-75">Working Days (Monthly)</small><br>
                                    <small class="fw-semibold" id="empWorkingDays">-</small>
                                </div>
                                <div class="col-6">
                                    <small class="opacity-75">Overtime Rate (Per Hour)</small><br>
                                    <small class="fw-semibold" id="empOvertimeRate">-</small>
                                </div>
                            </div>
                        </div>

                        {{-- Hint --}}
                        <div id="breakdownHint" class="text-muted text-center py-5 px-3">
                            <i class="bi bi-info-circle-fill fs-1 mb-3 d-block opacity-50"></i>
                            Select an employee to view salary breakdown
                        </div>

                        {{-- Error --}}
                        <div id="breakdownError" class="alert alert-danger m-3 d-none"></div>

                        {{-- Loading --}}
                        <div id="breakdownLoading" class="text-center text-muted py-5 d-none">
                            <div class="spinner-border spinner-border-sm me-2 text-primary"></div>
                            Calculating...
                        </div>

                        {{-- Result --}}
                        <div id="breakdownResult" class="d-none">
                            {{-- Basic / Total Monthly --}}
                            <div style="padding: 15px; border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Basic Salary</span>
                                    <span class="fw-semibold" id="basicSalary">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Increment</span>
                                    <span class="fw-semibold text-success" id="incrementAmount">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-1 border-top">
                                    <span class="fw-bold">Total Monthly</span>
                                    <span class="fw-bold text-primary" id="totalMonthly">AED 0</span>
                                </div>
                            </div>

                            {{-- Daily Rate / Days Worked --}}
                            <div style="padding: 12px 15px; border-bottom: 1px solid #e9ecef; background: #f8f9fa;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Daily Rate</span>
                                    <span id="dailyRate" class="text-muted">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Days Worked (<span id="daysWorkedLabel">0/0</span>)</span>
                                    <span class="fw-semibold" id="daysWorkedAmount">AED 0</span>
                                </div>
                            </div>

                            {{-- Overtime --}}
                            <div style="padding: 12px 15px; border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Overtime (<span id="overtimeLabel">0h</span> ×
                                        <span id="overtimeRate">0</span>)</span>
                                    <span class="fw-semibold text-success" id="overtimeAmount">AED 0</span>
                                </div>
                            </div>

                            {{-- Gross Salary --}}
                            <div style="padding: 15px; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">Gross Salary</span>
                                    <span class="fw-bold fs-3 text-success" id="grossSalary">AED 0</span>
                                </div>
                            </div>

                            {{-- Deductions --}}
                            <div style="padding: 15px; border-bottom: 1px solid #e9ecef;">
                                <h6 class="fw-bold mb-3" style="color: #dc3545;">Deductions</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Mess/Food</span>
                                    <span class="text-danger" id="foodDeductionDisplay">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Visa</span>
                                    <span class="text-danger" id="visaDeductionDisplay">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Insurance</span>
                                    <span class="text-danger" id="insuranceDeductionDisplay">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Advance</span>
                                    <span class="text-danger" id="advanceDeductionDisplay">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Other</span>
                                    <span class="text-danger" id="otherDeductionDisplay">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 mt-1 border-top">
                                    <span class="fw-bold">Total Deductions</span>
                                    <span class="fw-bold text-danger" id="totalDeductions">AED 0</span>
                                </div>
                            </div>

                            {{-- Net Salary --}}
                            <div style="padding: 20px 15px; background: #f8f9fa;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-4">Net Salary</span>
                                    <span class="fw-bold fs-2" style="color: #2B5797;" id="netSalary">AED 0</span>
                                </div>
                            </div>

                            {{-- Salary Split --}}
                            <div style="margin: 15px; background: #e8f0fe; border-radius: 10px; padding: 12px;">
                                <h6 class="fw-bold mb-2" style="color: #1e40af;">Salary Split</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>1st Transfer (WPS)</span>
                                    <span class="fw-bold" id="wpsFirstTransfer">AED 0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>2nd Transfer (Balance)</span>
                                    <span class="fw-bold" style="color: #991b1b;" id="wpsSecondTransfer">AED 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                let previewRequestId = 0;

                // ── DOM Elements ─────────────────────────────────────────
                const employeeSelect         = $('#employeeSelect');
                const monthInput             = $('#monthInput');
                const workingDaysInput       = $('#workingDaysInput');
                const presentDaysInput       = $('#presentDaysInput');
                const overtimeHoursInput     = $('#overtimeHoursInput');
                const foodDeductionInput     = $('#foodDeductionInput');
                const visaDeductionInput     = $('#visaDeductionInput');
                const insuranceDeductionInput= $('#insuranceDeductionInput');
                const advanceDeductionInput  = $('#advanceDeductionInput');
                const otherDeductionInput    = $('#otherDeductionInput');
                const wpsFirstTransferInput  = $('#wpsFirstTransferInput');

                const resultBox        = $('#breakdownResult');
                const errorBox         = $('#breakdownError');
                const loadingBox       = $('#breakdownLoading');
                const hintBox          = $('#breakdownHint');
                const employeeInfoCard = $('#employeeInfoCard');
                const breakdownActions = $('#breakdownActions');

                // ── Helpers ───────────────────────────────────────────────
                function formatAED(amount) {
                    const num = parseFloat(amount) || 0;
                    return 'AED ' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function setText(id, value) {
                    $('#' + id).text(value);
                }

                function debounce(func, wait) {
                    let timeout;
                    return function (...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }

                const debouncedFetchPreview = debounce(fetchPreview, 400);

                // ── getPayload ────────────────────────────────────────────
                // advance_deduction: value hai toh wo bhejo, blank/0 toh null (DB se auto)
                function getPayload(saveStatus = null) {
                    const advVal = advanceDeductionInput.val();
                    return {
                        employee_id:         employeeSelect.val(),
                        month:               monthInput.val(),
                        working_days:        workingDaysInput.val() || 30,
                        present_days:        presentDaysInput.val() || 0,
                        overtime_hours:      overtimeHoursInput.val() || 0,
                        food_deduction:      foodDeductionInput.val() || 0,
                        visa_deduction:      visaDeductionInput.val() || 0,
                        insurance_deduction: insuranceDeductionInput.val() || 0,
                        advance_deduction:   (advVal !== '' && advVal !== null)
                                                ? parseFloat(advVal)
                                                : null,
                        other_deduction:     otherDeductionInput.val() || 0,
                        wps_first_transfer:  wpsFirstTransferInput.val() || 0,
                        save_status:         saveStatus,
                    };
                }

                // ── loadEmployeeDefaults ──────────────────────────────────
                async function loadEmployeeDefaults() {
                    const employeeId = employeeSelect.val();
                    if (!employeeId) return;

                    // Loading state
                    $('#advanceHint').html('<small class="text-muted"><i class="bi bi-hourglass-split"></i> Loading...</small>');

                    try {
                        const response = await fetch(
                            `{{ route('payroll.employee-defaults', '') }}/${employeeId}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                        const result = await response.json();

                        if (!result.success || !result.data) return;

                        const data = result.data;

                        // ✅ Sab fields prefill karo
                        if (data.food_deduction         !== undefined) foodDeductionInput.val(data.food_deduction);
                        if (data.visa_deduction          !== undefined) visaDeductionInput.val(data.visa_deduction);
                        if (data.insurance_deduction     !== undefined) insuranceDeductionInput.val(data.insurance_deduction);
                        if (data.other_deduction         !== undefined) otherDeductionInput.val(data.other_deduction);
                        if (data.wps_first_transfer      !== undefined) wpsFirstTransferInput.val(data.wps_first_transfer);
                        if (data.working_days_per_month  !== undefined) workingDaysInput.val(data.working_days_per_month);
                        if (data.present_days            !== undefined) presentDaysInput.val(data.present_days);
                        if (data.overtime_hours          !== undefined) overtimeHoursInput.val(data.overtime_hours);

                        // ✅ Advance — set karo, blank mat karo
                        const adv = parseFloat(data.advance_deduction) || 0;
                        advanceDeductionInput.val(adv);

                        if (adv > 0) {
                            $('#advanceHint').html(
                                `<span class="text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Auto calculated: AED ${adv.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    <small class="text-muted">(editable)</small>
                                </span>`
                            );
                            advanceDeductionInput.addClass('border-success');
                        } else {
                            $('#advanceHint').html('<small class="text-muted">No advance this month</small>');
                            advanceDeductionInput.removeClass('border-success');
                        }

                        // Employee info card
                        const opt = employeeSelect.find('option:selected');
                        $('#empName').text(opt.text().trim());
                        $('#empCode').text(opt.data('code') || '-');
                        $('#empCompany').text(opt.data('company') || '-');
                        $('#empDept').text(opt.data('department') || '-');
                        $('#empDesig').text(opt.data('designation') || '-');
                        $('#empWorkingDays').text(data.working_days_per_month ?? '-');
                        $('#empOvertimeRate').text(data.overtime_rate_per_hour ?? '-');

                        employeeInfoCard.removeClass('d-none');

                        // ✅ Prefill hone ke baad turant preview fetch karo
                        await fetchPreview();

                    } catch (error) {
                        console.error('Error loading employee defaults:', error);
                        $('#advanceHint').html('<span class="text-danger">Error loading details</span>');
                    }
                }

                // ── fetchPreview ──────────────────────────────────────────
                async function fetchPreview() {
                    const employeeId = employeeSelect.val();
                    const month      = monthInput.val();

                    if (!employeeId || !month) {
                        resultBox.addClass('d-none');
                        hintBox.removeClass('d-none');
                        breakdownActions.addClass('d-none');
                        return;
                    }

                    const requestId = ++previewRequestId;

                    hintBox.addClass('d-none');
                    errorBox.addClass('d-none');
                    resultBox.addClass('d-none');
                    loadingBox.removeClass('d-none');
                    breakdownActions.addClass('d-none');

                    try {
                        const response = await fetch('{{ route('payroll.preview.breakdown') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(getPayload(null)),
                        });

                        const result = await response.json();
                        if (requestId !== previewRequestId) return;

                        if (!result.success) throw new Error(result.message || 'Failed to calculate');

                        const d = result.data;

                        // ✅ Breakdown update
                        setText('basicSalary',            formatAED(d.basic_salary));
                        setText('incrementAmount',         formatAED(d.increment_amount ?? 0));
                        setText('totalMonthly',            formatAED(d.total_monthly));
                        setText('dailyRate',               formatAED(d.daily_rate));
                        setText('daysWorkedLabel',         `${d.present_days || 0}/${d.working_days || 30}`);
                        setText('daysWorkedAmount',        formatAED(d.days_worked_amount));
                        setText('overtimeLabel',           d.overtime_hours || 0);
                        setText('overtimeRate',            d.overtime_rate || 0);
                        setText('overtimeAmount',          formatAED(d.overtime_amount));
                        setText('grossSalary',             formatAED(d.gross_salary));
                        setText('foodDeductionDisplay',    formatAED(d.food_deduction));
                        setText('visaDeductionDisplay',    formatAED(d.visa_deduction));
                        setText('insuranceDeductionDisplay', formatAED(d.insurance_deduction));
                        setText('advanceDeductionDisplay', formatAED(d.advance_deduction));
                        setText('otherDeductionDisplay',   formatAED(d.other_deduction));
                        setText('totalDeductions',         formatAED(d.total_deductions));
                        setText('netSalary',               formatAED(d.net_salary));
                        setText('wpsFirstTransfer',        formatAED(d.wps_first_transfer));
                        setText('wpsSecondTransfer',       formatAED(d.wps_second_transfer));

                        // Company info from calculation
                        $('#empWorkingDays').text(d.working_days ?? '-');
                        $('#empOvertimeRate').text(d.overtime_rate ?? '-');

                        resultBox.removeClass('d-none');
                        breakdownActions.removeClass('d-none');

                    } catch (error) {
                        if (requestId !== previewRequestId) return;
                        errorBox.text(error.message || 'Something went wrong').removeClass('d-none');
                    } finally {
                        if (requestId !== previewRequestId) return;
                        loadingBox.addClass('d-none');
                    }
                }

                // ── Form Submit ───────────────────────────────────────────
                function submitForm(status) {
                    if (!employeeSelect.val() || !monthInput.val()) {
                        alert('Please select employee and month');
                        return;
                    }
                    $('#saveStatusInput').val(status);
                    $('#payrollForm').submit();
                }

                // ── Reset ─────────────────────────────────────────────────
                function resetForm() {
                    presentDaysInput.val(30);
                    overtimeHoursInput.val(0);
                    foodDeductionInput.val(0);
                    visaDeductionInput.val(0);
                    insuranceDeductionInput.val(0);
                    advanceDeductionInput.val(0);
                    otherDeductionInput.val(0);
                    wpsFirstTransferInput.val(0);
                    workingDaysInput.val(30);

                    $('#advanceHint').html('<small class="text-muted">Loading...</small>');
                    advanceDeductionInput.removeClass('border-success');

                    resultBox.addClass('d-none');
                    hintBox.removeClass('d-none');
                    employeeInfoCard.addClass('d-none');
                    breakdownActions.addClass('d-none');
                    errorBox.addClass('d-none');
                }

                // ── Event Listeners ───────────────────────────────────────

                // Employee change — prefill + preview
                employeeSelect.on('change', function () {
                    if ($(this).val()) {
                        resetForm();
                        loadEmployeeDefaults(); // fetchPreview is called inside after prefill
                    } else {
                        resetForm();
                    }
                });

                // Month change
                monthInput.on('change', function () {
                    if (employeeSelect.val()) {
                        loadEmployeeDefaults();
                    }
                });

                // ✅ Koi bhi deduction/attendance field badlo — turant preview update ho
                const liveFields = [
                    presentDaysInput,
                    overtimeHoursInput,
                    workingDaysInput,
                    foodDeductionInput,
                    visaDeductionInput,
                    insuranceDeductionInput,
                    advanceDeductionInput,
                    otherDeductionInput,
                    wpsFirstTransferInput,
                ];

                liveFields.forEach(function (field) {
                    field.on('input change', debouncedFetchPreview);
                });

                // Save buttons
                $('#saveDraftBtn').on('click', () => submitForm('draft'));
                $('#savePaidBtn').on('click',  () => submitForm('paid'));

                // ── Init ──────────────────────────────────────────────────
                if (employeeSelect.val()) {
                    loadEmployeeDefaults();
                }
            });
        </script>
    @endpush

    <style>
        .sticky-top {
            position: sticky;
            top: 20px;
            z-index: 100;
        }
        .border-success {
            border-color: #198754 !important;
        }
    </style>
@endsection