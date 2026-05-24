{{-- resources/views/employees/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Employee')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section with Gradient --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                <i class="bi bi-person-plus fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Add New Employee</h1>
                <p class="text-muted mb-0">Fill in the details below to create a new employee record</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" id="employeeForm">
        @csrf

        <div class="row g-4">
            {{-- Left Column --}}
            <div class="col-lg-4">
                {{-- Photo Upload Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="position-relative d-inline-block mb-3">
                            <div id="photoPreviewContainer" class="position-relative">
                                <img src="https://ui-avatars.com/api/?name=New+Employee&size=200&background=2B5797&color=fff&rounded=true&bold=true"
                                    id="preview" class="rounded-circle border border-4 border-white shadow-lg"
                                    style="width:150px;height:150px;object-fit:cover;">
                                <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2" style="cursor:pointer;" onclick="document.getElementById('photo').click()">
                                    <i class="bi bi-camera-fill text-white small"></i>
                                </div>
                            </div>
                        </div>
                        <div class="upload-info">
                            <p class="mb-1 fw-semibold">Upload Profile Photo</p>
                            <small class="text-muted">PNG, JPG up to 2MB</small>
                            <input type="file" id="photo" name="photo" class="d-none" accept="image/*" onchange="previewPhoto(this)">
                        </div>
                        @error('photo')
                            <div class="alert alert-danger py-1 small mt-2 mb-0">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Employee ID Card --}}
                <div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-qr-code fs-1 mb-2 opacity-75"></i>
                        <p class="text-uppercase small mb-1 opacity-75">Employee Identification Number</p>
                        <h3 class="fw-bold mb-0 tracking-wide">{{ \App\Models\Employee::generateEmployeeCode() }}</h3>
                    </div>
                </div>

                {{-- Quick Stats Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Quick Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Created By</small>
                            <small class="fw-semibold">{{ auth()->user()->name ?? 'System' }}</small>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Date</small>
                            <small class="fw-semibold">{{ now()->format('d M Y') }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Time</small>
                            <small class="fw-semibold">{{ now()->format('h:i A') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-lg-8">
                {{-- Personal Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-fill text-muted"></i></span>
                                            <input type="text" name="first_name" id="first_name"
                                                class="form-control @error('first_name') is-invalid @enderror border-start-0"
                                                value="{{ old('first_name') }}" required placeholder="First Name">
                                        </div>
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
                                <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="departmentSelect"
                                    class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
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

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Joining Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="joining_date"
                                        class="form-control @error('joining_date') is-invalid @enderror"
                                        value="{{ old('joining_date') }}">
                                </div>
                                @error('joining_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror" 
                                        value="{{ old('email') }}" placeholder="john@company.com">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror" 
                                        value="{{ old('phone') }}" placeholder="+971 ...">
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nationality</label>
                                <select name="country_id" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }} class="text-success">Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }} class="text-secondary">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payroll Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-calculator-fill me-2 text-primary"></i>Payroll Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">WPS Number</label>
                                <input type="text" name="wps_personal_number" class="form-control"
                                    value="{{ old('wps_personal_number') }}" placeholder="WPS-XXX-XXXXX">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payroll Company</label>
                                <input type="text" class="form-control bg-light" value="{{ $companyName ?? 'Not configured' }}" readonly>
                                <input type="hidden" name="custom_fields[payroll_company]" value="{{ old('custom_fields.payroll_company', $companyName ?? '') }}">
                                <small class="text-muted">Pulled from Company Master settings.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents Section - Accordion Style (Most Compact) --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                            Documents
                            <small class="text-muted fs-6 fw-normal ms-2">(Optional)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="documentsAccordion">
                            @php
                                $docTypes = [
                                    'passport' => ['icon' => 'bi-passport', 'label' => 'Passport'],
                                    'emirates_id' => ['icon' => 'bi-card-identity', 'label' => 'Emirates ID'],
                                    'labour_card' => ['icon' => 'bi-briefcase', 'label' => 'Labour Card'],
                                    'driving_license' => ['icon' => 'bi-car-front', 'label' => 'Driving License'],
                                ];
                            @endphp

                            @foreach ($docTypes as $type => $data)
                                <div class="accordion-item border-0 mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed bg-light rounded p-2" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ ucfirst($type) }}" style="font-size: 0.9rem;">
                                            <i class="bi {{ $data['icon'] }} me-2 text-primary"></i>
                                            {{ $data['label'] }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ ucfirst($type) }}" class="accordion-collapse collapse" data-bs-parent="#documentsAccordion">
                                        <div class="accordion-body p-3">
                                            <div class="row g-2">
                                                <div class="col-md-5">
                                                    <label class="form-label small text-muted mb-1">Number</label>
                                                    <input type="text" name="documents[{{ $type }}][number]"
                                                        class="form-control form-control-sm" 
                                                        value="{{ old("documents.$type.number") }}"
                                                        placeholder="Document number">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small text-muted mb-1">Expiry Date</label>
                                                    <input type="date" name="documents[{{ $type }}][expiry_date]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old("documents.$type.expiry_date") }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-muted mb-1">Upload</label>
                                                    <input type="file" name="documents[{{ $type }}][file]"
                                                        class="form-control form-control-sm" accept="image/*,application/pdf">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Insurance Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i>Insurance Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Provider</label>
                                <input type="text" name="custom_fields[insurance_provider]" class="form-control"
                                    value="{{ old('custom_fields.insurance_provider') }}" placeholder="e.g., DAMAN, AXA">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Policy Number</label>
                                <input type="text" name="custom_fields[insurance_policy_number]" class="form-control"
                                    value="{{ old('custom_fields.insurance_policy_number') }}" placeholder="Policy number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="custom_fields[insurance_start_date]" class="form-control"
                                    value="{{ old('custom_fields.insurance_start_date') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="custom_fields[insurance_end_date]" class="form-control"
                                    value="{{ old('custom_fields.insurance_end_date') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary & Compensation --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-currency-dollar me-2 text-primary"></i>Salary & Compensation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Basic Salary <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="basic_salary" step="0.01"
                                        class="form-control @error('basic_salary') is-invalid @enderror"
                                        value="{{ old('basic_salary', 0) }}" min="0" required>
                                </div>
                                @error('basic_salary')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Increment Value</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="increment_value" step="0.01" class="form-control"
                                        value="{{ old('increment_value', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Overtime Rate (Hourly)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="overtime_rate_per_hour" step="0.01" class="form-control"
                                        value="{{ old('overtime_rate_per_hour', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">WPS Salary / 1st Transfer</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="wps_first_transfer_amount" step="0.01"
                                        class="form-control" value="{{ old('wps_first_transfer_amount', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="fw-bold mb-3"><i class="bi bi-piggy-bank me-2"></i>Fixed Monthly Deductions</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mess / Food</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="food_deduction" step="0.01" class="form-control"
                                        value="{{ old('food_deduction', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Visa Total Charges</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="visa_deduction" step="0.01" class="form-control"
                                        value="{{ old('visa_deduction', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Visa Installments</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                                    <input type="number" name="visa_total_installments" step="1" class="form-control"
                                        value="{{ old('visa_total_installments', 1) }}" min="1" max="120">
                                    <span class="input-group-text">months</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Insurance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="insurance_deduction" step="0.01" class="form-control"
                                        value="{{ old('insurance_deduction', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Advance Payment</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="advance_payment" step="0.01" class="form-control"
                                        value="{{ old('advance_payment', 0) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Other Deductions</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="other_deduction" step="0.01" class="form-control"
                                        value="{{ old('other_deduction', 0) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Custom Fields --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-puzzle me-2 text-primary"></i>Custom Fields</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomField()">
                            <i class="bi bi-plus-circle me-1"></i>Add Field
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="customFieldsContainer"></div>
                        <div class="text-muted small mt-2">
                            <i class="bi bi-info-circle-fill me-1"></i> Add custom fields as per your requirement.
                        </div>
                    </div>
                </div>

                {{-- Submit Actions --}}
                <div class="d-flex justify-content-end gap-3 mb-4">
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-lg me-1"></i> Create Employee
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
    }
    .transition {
        transition: all 0.3s ease;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);
    }
    .tracking-wide {
        letter-spacing: 2px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2B5797;
        box-shadow: 0 0 0 0.2rem rgba(43,87,151,0.15);
    }
    .btn-primary {
        background-color: #2B5797;
        border-color: #2B5797;
    }
    .btn-primary:hover {
        background-color: #1E3A6F;
        border-color: #1E3A6F;
    }
    .btn-outline-primary {
        color: #2B5797;
        border-color: #2B5797;
    }
    .btn-outline-primary:hover {
        background-color: #2B5797;
        border-color: #2B5797;
    }
    .card {
        border-radius: 1rem;
    }
    .card-header {
        border-radius: 1rem 1rem 0 0 !important;
    }
    
    /* Accordion custom styling */
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #2B5797;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(43,87,151,0.25);
    }
    .accordion-button::after {
        background-size: 0.875rem;
    }
</style>

<script>
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('preview');
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    let customFieldIndex = 0;

    function addCustomField() {
        const container = document.getElementById('customFieldsContainer');
        const fieldHtml = `
            <div class="row g-2 mb-2 custom-field-row align-items-center" data-index="${customFieldIndex}">
                <div class="col-md-5">
                    <input type="text" name="dynamic_custom_fields[${customFieldIndex}][name]" class="form-control form-control-sm" placeholder="Field Name" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="dynamic_custom_fields[${customFieldIndex}][value]" class="form-control form-control-sm" placeholder="Field Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeCustomField(this)">
                        <i class="bi bi-trash me-1"></i> Remove
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
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
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
