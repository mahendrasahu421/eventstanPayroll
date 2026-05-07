@extends('layouts.app')

@section('title', 'Create Employee')

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <i class="bi bi-person-plus fs-2 text-primary"></i>
        <h2>Add New Employee</h2>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" id="employeeForm">
        @csrf

        <div class="row g-4">
            <div class="col-lg-4">
                {{-- Photo --}}
                <div class="card p-4 text-center">
                    <div class="upload-zone mb-3" onclick="document.getElementById('photo').click()" style="cursor:pointer;">
                        <i class="bi bi-person fs-1 text-muted mb-2" id="photoIcon"></i>
                        <p class="mb-1 fw-semibold">Upload Photo</p>
                        <small class="text-muted">PNG, JPG up to 2MB</small>
                        <input type="file" id="photo" name="photo" class="d-none" accept="image/*"
                            onchange="previewPhoto(this)">
                    </div>

                    <img src="https://ui-avatars.com/api/?name=New+Employee&size=200&background=2B5797&color=fff"
                        id="preview" class="rounded-circle mx-auto d-none border border-4 border-white shadow-lg"
                        style="width:150px;height:150px;object-fit:cover;">

                    @error('photo')
                        <div class="alert alert-danger py-1 small mt-2 mb-0">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Employee ID --}}
                <div class="card mt-4">
                    <div class="card-body text-center py-3">
                        <label class="form-label h5 mb-1">Employee ID</label>
                        <input type="text" class="form-control form-control-lg bg-light text-center fw-bold"
                            style="max-width: 300px; margin: 0 auto;"
                            value="{{ \App\Models\Employee::generateEmployeeCode() }}" readonly>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Personal Details --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="first_name" id="first_name"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            value="{{ old('first_name') }}" required placeholder="First Name">
                                        @error('first_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="last_name" id="last_name"
                                            class="form-control @error('last_name') is-invalid @enderror"
                                            value="{{ old('last_name') }}" required placeholder="Last Name">
                                        @error('last_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
<div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <select name="department_id" id="departmentSelect"
                                    class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation</label>
                                <select name="designation_id" id="designationSelect"
                                    class="form-select @error('designation_id') is-invalid @enderror" required>
                                    <option value="">Select Designation</option>
                                    @foreach ($designations as $des)
                                        <option value="{{ $des->id }}"
                                            data-department-id="{{ $des->department_id ?? '' }}"
                                            {{ old('designation_id') == $des->id ? 'selected' : '' }}>{{ $des->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('designation_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            


                            <div class="col-md-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                    placeholder="+971 ...">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    placeholder="john@company.com">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nationality</label>
                                <select name="country_id" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-4">
                                <label class="form-label">WPS Number</label>
                                <input type="text" name="wps_personal_number" class="form-control"
                                    value="{{ old('wps_personal_number') }}" placeholder="WPS-...">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Payroll Company</label>

                                <select name="custom_fields[payroll_company]" class="form-select">
                                    <option value="">Select Payroll Company</option>

                                    @foreach ($company as $comp)
                                        <option value="{{ $comp->id }}"
                                            {{ old('custom_fields.payroll_company', $selectedCompanyId ?? '') == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Join Date *</label>
                                <input type="date" name="joining_date"
                                    class="form-control @error('joining_date') is-invalid @enderror"
                                    value="{{ old('joining_date') }}" required>
                                @error('joining_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                        Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                $docTypes = [
                                    'passport' => 'Passport',
                                    'emirates_id' => 'Emirates ID',
                                    'labour_card' => 'Labour Card',
                                    'driving_license' => 'Driving License',
                                ];
                            @endphp

                            @foreach ($docTypes as $type => $label)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded h-100">
                                        <h6 class="fw-bold mb-3">{{ $label }}</h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Number</label>
                                                <input type="text" name="documents[{{ $type }}][number]"
                                                    class="form-control" value="{{ old("documents.$type.number") }}"
                                                    placeholder="Document number">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="date" name="documents[{{ $type }}][expiry_date]"
                                                    class="form-control"
                                                    value="{{ old("documents.$type.expiry_date") }}">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Upload PDF / Image</label>
                                                <input type="file" name="documents[{{ $type }}][file]"
                                                    class="form-control" accept="image/*,application/pdf">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Insurance Details --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Insurance Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Insurance Provider</label>
                                <input type="text" name="custom_fields[insurance_provider]" class="form-control"
                                    value="{{ old('custom_fields.insurance_provider') }}" placeholder="DAMAN">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Policy Number</label>
                                <input type="text" name="custom_fields[insurance_policy_number]" class="form-control"
                                    value="{{ old('custom_fields.insurance_policy_number') }}"
                                    placeholder="Policy number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="custom_fields[insurance_start_date]" class="form-control"
                                    value="{{ old('custom_fields.insurance_start_date') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="custom_fields[insurance_end_date]" class="form-control"
                                    value="{{ old('custom_fields.insurance_end_date') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary & Compensation --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Salary & Compensation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Basic Salary (AED) *</label>
                                <input type="number" name="basic_salary" step="0.01"
                                    class="form-control @error('basic_salary') is-invalid @enderror"
                                    value="{{ old('basic_salary', 0) }}" min="0" required>
                                @error('basic_salary')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Increment Value (AED)</label>
                                <input type="number" name="increment_value" step="0.01" class="form-control"
                                    value="{{ old('increment_value', 0) }}" min="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Overtime Hourly Rate (AED)</label>
                                <input type="number" name="overtime_rate_per_hour" step="0.01" class="form-control"
                                    value="{{ old('overtime_rate_per_hour', 0) }}" min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">WPS Salary / 1st Transfer (AED)</label>
                                <input type="number" name="wps_first_transfer_amount" step="0.01"
                                    class="form-control" value="{{ old('wps_first_transfer_amount', 0) }}"
                                    min="0">
                            </div>

                            <div class="col-12 mt-2">
                                <hr class="my-3">
                                <h6 class="mb-2">Fixed Monthly Deductions (auto-applied to payroll)</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mess / Food (AED)</label>
                                <input type="number" name="food_deduction" step="0.01" class="form-control"
                                    value="{{ old('food_deduction', 0) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Visa Charges (AED)</label>
                                <input type="number" name="visa_deduction" step="0.01" class="form-control"
                                    value="{{ old('visa_deduction', 0) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Insurance (AED)</label>
                                <input type="number" name="insurance_deduction" step="0.01" class="form-control"
                                    value="{{ old('insurance_deduction', 0) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Advance Payment (AED)</label>
                                <input type="number" name="advance_payment" step="0.01" class="form-control"
                                    value="{{ old('advance_payment', 0) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Other (AED)</label>
                                <input type="number" name="other_deduction" step="0.01" class="form-control"
                                    value="{{ old('other_deduction', 0) }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Custom Fields --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Custom Fields</h5>
                    </div>
                    <div class="card-body">
                        <div id="customFieldsContainer"></div>
                        <button type="button" class="btn btn-outline-primary mt-2" onclick="addCustomField()">
                            <i class="bi bi-plus-circle me-1"></i>Add Field
                        </button>
                        <div class="text-muted small mt-2">Add or remove fields as per your requirement.</div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Create Employee
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('preview');
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                    document.querySelector('.upload-zone').style.display = 'none';
                    document.getElementById('photoIcon').className = 'bi bi-check-circle-fill fs-1 text-success mb-2';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        let customFieldIndex = 0;

        function addCustomField() {
            const container = document.getElementById('customFieldsContainer');
            const fieldHtml = `
                <div class="row g-2 mb-2 custom-field-row" data-index="${customFieldIndex}">
                    <div class="col-md-5">
                        <input type="text" name="dynamic_custom_fields[${customFieldIndex}][name]" class="form-control form-control-sm" placeholder="Field Name" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="dynamic_custom_fields[${customFieldIndex}][value]" class="form-control form-control-sm" placeholder="Field Value">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeCustomField(this)">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', fieldHtml);
            customFieldIndex++;
        }

        function removeCustomField(button) {
            button.closest('.custom-field-row').remove();
        }

        // Department -> Designation dependent dropdown
        document.addEventListener('DOMContentLoaded', () => {
            const departmentSelect = document.getElementById('departmentSelect');
            const designationSelect = document.getElementById('designationSelect');
            if (!departmentSelect || !designationSelect) return;

            const selectedDesignationId = {{ old('designation_id') ? old('designation_id') : 'null' }};

            async function loadDesignations(departmentId, designationId = null) {
                if (!departmentId) {
                    designationSelect.innerHTML = '<option value="">Select Designation</option>';
                    return;
                }

                designationSelect.innerHTML = '<option value="">Loading...</option>';


                const url = `{{ url('/employees/designations') }}/${departmentId}`;
                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();

                    let options = '<option value="">Select Designation</option>';
                    data.designations.forEach(d => {
                        const selected = (designationId && Number(d.id) === Number(designationId)) ? 'selected' : '';
                        options += `<option value="${d.id}" ${selected}>${d.name}</option>`;
                    });

                    designationSelect.innerHTML = options;
                } catch (e) {
                    console.error(e);
                    designationSelect.innerHTML = '<option value="">Select Designation</option>';
                }
            }

            // initial load if department already selected
            const initialDeptId = departmentSelect.value;
            if (initialDeptId) {
                loadDesignations(initialDeptId, selectedDesignationId);
            }

            departmentSelect.addEventListener('change', function() {
                loadDesignations(this.value, null);
            });
        });
    </script>
@endsection

