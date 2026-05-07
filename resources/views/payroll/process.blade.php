@extends('layouts.app')

@section('title', 'Process Payroll')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-calculator fs-2 text-primary"></i>
        <h2>Process Single Employee Payroll</h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('payroll.calculate') }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Payroll Inputs</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Month *</label>
                                <input type="month" name="month"
                                    class="form-control @error('month') is-invalid @enderror"
                                    value="{{ old('month', $month ?? now()->format('Y-m')) }}" required>
                                @error('month')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee *</label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Save Status *</label>
                                <select name="save_status" class="form-select @error('save_status') is-invalid @enderror"
                                    required>
                                    <option value="draft" {{ old('save_status') === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="paid" {{ old('save_status') === 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                </select>
                                @error('save_status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4" />

                        <div class="row g-3 mb-2">
                            <div class="col-md-12">
                                <label class="form-label">Working Days</label>
                                <input type="number" name="working_days" value="30" min="1" max="31" class="form-control" readonly>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Present Days *</label>
                                <input type="number" name="present_days"
                                    class="form-control @error('present_days') is-invalid @enderror"
                                    value="{{ old('present_days') }}" min="0" max="31" required>
                                @error('present_days')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-md-3">
                                <label class="form-label">Leave Days</label>
                                <input type="number" name="leave_days" class="form-control"
                                    value="{{ old('leave_days', 0) }}" min="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Overtime Hours</label>
                                <input type="number" name="overtime_hours" class="form-control"
                                    value="{{ old('overtime_hours', 0) }}" step="0.5" min="0">
                            </div>
                        </div>

                        <hr class="my-4" />

                        <div class="row g-3 mb-2">
                            <div class="col-md-3">
                                <label class="form-label">Food Deduction</label>
                                <input type="number" name="food_deduction"
                                    class="form-control @error('food_deduction') is-invalid @enderror"
                                    value="{{ old('food_deduction', 0) }}" step="0.01" min="0">
                                @error('food_deduction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Visa Deduction</label>
                                <input type="number" name="visa_deduction"
                                    class="form-control @error('visa_deduction') is-invalid @enderror"
                                    value="{{ old('visa_deduction', 0) }}" step="0.01" min="0">
                                @error('visa_deduction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Insurance Deduction</label>
                                <input type="number" name="insurance_deduction"
                                    class="form-control @error('insurance_deduction') is-invalid @enderror"
                                    value="{{ old('insurance_deduction', 0) }}" step="0.01" min="0">
                                @error('insurance_deduction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Advance Deduction</label>
                                <input type="number" name="advance_deduction"
                                    class="form-control @error('advance_deduction') is-invalid @enderror"
                                    value="{{ old('advance_deduction', '') }}" step="0.01" min="0"
                                    placeholder="Leave blank for auto">
                                @error('advance_deduction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Other Deduction</label>
                                <input type="number" name="other_deduction"
                                    class="form-control @error('other_deduction') is-invalid @enderror"
                                    value="{{ old('other_deduction', 0) }}" step="0.01" min="0">
                                @error('other_deduction')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">WPS First Transfer Amount</label>
                                <input type="number" name="wps_first_transfer"
                                    class="form-control @error('wps_first_transfer') is-invalid @enderror"
                                    value="{{ old('wps_first_transfer', '') }}" step="0.01" min="0"
                                    placeholder="Leave blank for default/auto">
                                @error('wps_first_transfer')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('payroll.history') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>For bulk processing</h5>
                    <a href="{{ route('payroll.bulk') }}" class="btn btn-sm btn-outline-primary">Bulk Payroll</a>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Salary Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-muted" id="breakdownHint">
                        Employee select hote hi right side par live breakdown aa jayega (without save).
                    </div>

                    <div id="breakdownError" class="alert alert-danger d-none"></div>

                    <div id="breakdownResult" class="d-none">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-2 border rounded">
                                    <div class="text-muted" style="font-size: 12px">Basic Salary</div>
                                    <div class="fs-5 fw-bold text-primary" id="basicSalary">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-6">Increment</div>
                                    <div class="col-6 text-end" id="incrementAmount">0</div>
                                    <div class="col-6">Total Monthly</div>
                                    <div class="col-6 text-end" id="totalMonthly">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-6">Daily Rate</div>
                                    <div class="col-6 text-end" id="dailyRate">0</div>
                                    <div class="col-6">Days Worked</div>
                                    <div class="col-6 text-end" id="daysWorked">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-6">Overtime (h × rate)</div>
                                    <div class="col-6 text-end" id="overtimeAmount">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-2 border rounded">
                                    <div class="text-muted" style="font-size: 12px">Gross Salary</div>
                                    <div class="fs-5 fw-bold text-primary" id="grossSalary">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mt-1">
                                    <div class="text-muted mb-2" style="font-size: 12px">Deductions</div>
                                    <div class="row g-2">
                                        <div class="col-6">Total Deductions</div>
                                        <div class="col-6 text-end" id="totalDeductions">0</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-2 border rounded">
                                    <div class="text-muted" style="font-size: 12px">Net Salary</div>
                                    <div class="fs-5 fw-bold text-success" id="netSalary">0</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mt-2">
                                    <div class="text-muted mb-2" style="font-size: 12px">WPS</div>
                                    <div class="row g-2">
                                        <div class="col-6">WPS First</div>
                                        <div class="col-6 text-end" id="wpsFirstTransfer">0</div>
                                        <div class="col-6">WPS Second</div>
                                        <div class="col-6 text-end" id="wpsSecondTransfer">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="breakdownLoading" class="alert alert-info d-none mt-3 mb-0" role="status">
                        Calculating...
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const calcRoute = '{{ route('payroll.calculate') }}';
            const previewUrl = '{{ route('payroll.preview.breakdown') }}';

            const form = document.querySelector(`form[action="${calcRoute}"]`) || document.querySelector('form');
            if (!form) return;


            const employeeSelect = form.querySelector('select[name="employee_id"]');
            const monthInput = form.querySelector('input[name="month"]');

            const workingDaysInput = form.querySelector('input[name="working_days"]');
            const presentDaysInput = form.querySelector('input[name="present_days"]');
            const leaveDaysInput = form.querySelector('input[name="leave_days"]');
            const overtimeHoursInput = form.querySelector('input[name="overtime_hours"]');

            const foodDeductionInput = form.querySelector('input[name="food_deduction"]');
            const visaDeductionInput = form.querySelector('input[name="visa_deduction"]');
            const insuranceDeductionInput = form.querySelector('input[name="insurance_deduction"]');
            const advanceDeductionInput = form.querySelector('input[name="advance_deduction"]');
            const otherDeductionInput = form.querySelector('input[name="other_deduction"]');

            const wpsFirstTransferInput = form.querySelector('input[name="wps_first_transfer"]');

            const resultBox = document.getElementById('breakdownResult');
            const errorBox = document.getElementById('breakdownError');
            const loadingBox = document.getElementById('breakdownLoading');

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            };

            const fmt = (n) => {
                const num = Number(n);
                if (!isFinite(num)) return '0';
                return new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(num);
            };

            function getVal(input) {
                if (!input) return null;
                const v = input.value;
                if (v === '' || v === null || typeof v === 'undefined') return null;
                return v;
            }

            let lastRequestId = 0;

            function safeNumber(v, fallback) {
                const n = Number(v);
                return isFinite(n) ? n : fallback;
            }

            async function fetchPreview() {
                const employeeId = employeeSelect?.value;
                const month = monthInput?.value;


        const working_days = getVal(workingDaysInput);
        const present_days = getVal(presentDaysInput);
        const leave_days = getVal(leaveDaysInput);
        const overtime_hours = getVal(overtimeHoursInput);

        if (!employeeId || !month) {
            resultBox?.classList.add('d-none');
            errorBox?.classList.add('d-none');
            return;
        }

        // Agar working/present empty hon to default/compute karna hai
        // (Server preview me required fields na bhejkar bhi calculation ho jayegi)
        const working_days_num = safeNumber(working_days, 26);
        const present_days_num = safeNumber(present_days, working_days_num);
        const leave_days_num = leave_days === null ? 0 : safeNumber(leave_days, 0);
        const overtime_hours_num = overtime_hours === null ? 0 : safeNumber(overtime_hours, 0);



                const payload = {
                    employee_id: employeeId,
                    month: month,
                    working_days: 30,
                    present_days: present_days,
                    leave_days: leave_days,
                    overtime_hours: overtime_hours,

                    food_deduction: getVal(foodDeductionInput),
                    visa_deduction: getVal(visaDeductionInput),
                    insurance_deduction: getVal(insuranceDeductionInput),
                    advance_deduction: getVal(advanceDeductionInput),
                    other_deduction: getVal(otherDeductionInput),
                    wps_first_transfer: getVal(wpsFirstTransferInput),
                };

                const requestId = ++lastRequestId;
                loadingBox?.classList.remove('d-none');
                errorBox?.classList.add('d-none');
                resultBox?.classList.add('d-none');

                try {
                    const res = await fetch('{{ route('payroll.preview.breakdown') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content') || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const json = await res.json();
                    if (!json || !json.success) throw new Error(json?.message || 'Preview failed');
                    if (requestId !== lastRequestId) return;

                    const d = json.data || {};

                    setText('basicSalary', fmt(d.basic_salary));
                    setText('incrementAmount', fmt(d.increment_amount));
                    setText('totalMonthly', fmt(d.total_monthly));
                    setText('dailyRate', fmt(d.daily_rate));

                    const daysWorkedText = d.present_days !== undefined && d.working_days !== undefined
                        ? `${d.present_days}/${d.working_days}`
                        : '0';
                    setText('daysWorked', daysWorkedText);

                    setText('overtimeAmount', fmt(d.overtime_amount));
                    setText('grossSalary', fmt(d.gross_salary));
                    setText('totalDeductions', fmt(d.total_deductions));
                    setText('netSalary', fmt(d.net_salary));

                    setText('wpsFirstTransfer', fmt(d.wps_first_transfer));
                    setText('wpsSecondTransfer', fmt(d.wps_second_transfer));

                    resultBox?.classList.remove('d-none');
                } catch (e) {
                    if (requestId !== lastRequestId) return;
                    errorBox.textContent = e?.message || 'Something went wrong';
                    errorBox?.classList.remove('d-none');
                } finally {
                    if (requestId !== lastRequestId) return;
                    loadingBox?.classList.add('d-none');
                }
            }

            const inputsToWatch = [
                employeeSelect,
                monthInput,
                workingDaysInput,
                presentDaysInput,
                leaveDaysInput,
                overtimeHoursInput,
                foodDeductionInput,
                visaDeductionInput,
                insuranceDeductionInput,
                advanceDeductionInput,
                otherDeductionInput,
                wpsFirstTransferInput,
            ].filter(Boolean);

            inputsToWatch.forEach(el => {
                el.addEventListener('change', fetchPreview);
                el.addEventListener('input', fetchPreview);
            });

            // Initial load if old values exist
            if (employeeSelect?.value && monthInput?.value) fetchPreview();
        })();
    </script>

@endsection
