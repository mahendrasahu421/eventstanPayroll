@extends('layouts.app')

@section('title', $employee->exists ? 'Edit Employee' : 'Create Employee')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                <i class="bi {{ $employee->exists ? 'bi-pencil-square' : 'bi-person-plus' }} fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">{{ $employee->exists ? 'Edit Employee' : 'Add New Employee' }}</h1>
                <p class="text-muted mb-0">{{ $employee->exists ? $employee->full_name . ' (' . $employee->employee_code . ')' : 'Fill in the details below' }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> View Profile
            </a>
        </div>
    </div>

    <form method="POST" action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}" enctype="multipart/form-data" id="employeeForm">
        @csrf 
        @if($employee->exists) @method('PUT') @endif

        <div class="row g-4">
            {{-- Left Column - Photo & Basic Info --}}
            <div class="col-lg-4">
                {{-- Photo Upload Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="position-relative d-inline-block mb-3">
                            <div id="photoPreviewContainer" class="position-relative">
                                @if($employee->photo)
                                    <img src="{{ Storage::url($employee->photo) }}" id="preview" 
                                         class="rounded-circle border border-4 border-white shadow-lg" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->first_name) }}+{{ urlencode($employee->last_name) }}&size=150&background=2B5797&color=fff&rounded=true&bold=true" 
                                         id="preview" class="rounded-circle border border-4 border-white shadow-lg" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                @endif
                                <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2" style="cursor: pointer;" onclick="document.getElementById('photo').click()">
                                    <i class="bi bi-camera-fill text-white small"></i>
                                </div>
                            </div>
                        </div>
                        <div class="upload-info">
                            <p class="mb-1 fw-semibold">Change Profile Photo</p>
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
                        <h3 class="fw-bold mb-0 tracking-wide">{{ $employee->exists ? $employee->employee_code : \App\Models\Employee::generateEmployeeCode() }}</h3>
                    </div>
                </div>

                {{-- Status Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-toggle2-on me-2 text-primary"></i>Employee Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Current Status</span>
                            <select name="status" class="form-select w-auto">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }} class="text-success">✓ Active</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }} class="text-secondary">✗ Inactive</option>
                            </select>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Joined Date</small>
                            <small class="fw-semibold">{{ $employee->joining_date?->format('d-m-Y') ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Form Sections --}}
            <div class="col-lg-8">
                {{-- Personal Information Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="first_name" id="first_name" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           value="{{ old('first_name', $employee->first_name) }}" required>
                                </div>
                                @error('first_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" name="last_name" id="last_name" 
                                           class="form-control @error('last_name') is-invalid @enderror" 
                                           value="{{ old('last_name', $employee->last_name) }}" required>
                                </div>
                                @error('last_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $employee->email) }}" placeholder="john@company.com">
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $employee->phone) }}" placeholder="+971 ...">
                                </div>
                                @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" name="date_of_birth" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                                </div>
                                @error('date_of_birth') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nationality</label>
                                <select name="country_id" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id', $employee->country_id) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Joining Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="joining_date" 
                                           class="form-control @error('joining_date') is-invalid @enderror" 
                                           value="{{ old('joining_date', $employee->joining_date) }}" required>
                                </div>
                                @error('joining_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Employment Details Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>Employment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <select name="department_id" id="departmentSelect" 
                                        class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation</label>
                                <select name="designation_id" id="designationSelect" 
                                        class="form-select @error('designation_id') is-invalid @enderror">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $des)
                                        <option value="{{ $des->id }}" 
                                                data-department-id="{{ $des->department_id ?? '' }}" 
                                                {{ old('designation_id', $employee->designation_id) == $des->id ? 'selected' : '' }}>
                                            {{ $des->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('designation_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">WPS Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-bank2"></i></span>
                                    <input type="text" name="wps_personal_number" class="form-control @error('wps_personal_number') is-invalid @enderror" 
                                           value="{{ old('wps_personal_number', $employee->wps_personal_number) }}" 
                                           inputmode="numeric" pattern="\d{14}" maxlength="14" placeholder="14 digit WPS number" required>
                                </div>
                                @error('wps_personal_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payroll Company</label>
                                <input type="text" class="form-control bg-light" value="{{ old('custom_fields.payroll_company', $companyName ?? $employee->custom_fields['payroll_company'] ?? 'Not configured') }}" readonly>
                                <input type="hidden" name="custom_fields[payroll_company]" value="{{ old('custom_fields.payroll_company', $companyName ?? $employee->custom_fields['payroll_company'] ?? '') }}">
                                <small class="text-muted">Pulled from Company Master settings.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Information Card (if exists) --}}
                @if($employee->salaryStructure)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-currency-dollar me-2 text-primary"></i>Salary Information</h5>
                        <a href="{{ route('employees.salary', $employee) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Salary
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Basic Salary</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="basic_salary" step="0.01" class="form-control" 
                                           value="{{ old('basic_salary', $employee->salaryStructure->basic_salary) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Overtime Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED/hr</span>
                                    <input type="number" name="overtime_rate_per_hour" step="0.01" class="form-control" 
                                           value="{{ old('overtime_rate_per_hour', $employee->salaryStructure->overtime_rate_per_hour) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">WPS First Transfer</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">AED</span>
                                    <input type="number" name="wps_first_transfer_amount" step="0.01" class="form-control" 
                                           value="{{ old('wps_first_transfer_amount', $employee->salaryStructure->wps_first_transfer_amount) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No salary structure found. <a href="{{ route('employees.salary', $employee) }}" class="alert-link">Set up salary first</a>.
                </div>
                @endif

                {{-- Documents Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Documents</h5>
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
                                @php
                                    $existingDoc = $employee->documents->where('document_type', $type)->first();
                                @endphp
                                <div class="accordion-item border-0 mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed bg-light rounded p-2" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ ucfirst($type) }}"
                                            style="font-size: 0.9rem;">
                                            <i class="bi {{ $data['icon'] }} me-2 text-primary"></i>
                                            {{ $data['label'] }}
                                            @if($existingDoc) <span class="badge bg-success ms-2 small">Saved</span> @endif
                                        </button>
                                    </h2>
                                    <div id="collapse{{ ucfirst($type) }}" class="accordion-collapse collapse">
                                        <div class="accordion-body p-3">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small text-muted mb-1">Number</label>
                                                    <input type="text" name="documents[{{ $type }}][number]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old("documents.$type.number", $existingDoc?->document_number ?? '') }}"
                                                        placeholder="Document number">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-muted mb-1">Issue Date</label>
                                                    <input type="date" name="documents[{{ $type }}][issue_date]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old("documents.$type.issue_date", $existingDoc?->issue_date?->format('d-m-Y') ?? '') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-muted mb-1">Expiry Date</label>
                                                    <input type="date" name="documents[{{ $type }}][expiry_date]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old("documents.$type.expiry_date", $existingDoc?->expiry_date?->format('d-m-Y') ?? '') }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small text-muted mb-1">File</label>
                                                    @if($existingDoc && $existingDoc->file_path)
                                                        <div class="mb-1">
                                                            <a href="{{ Storage::url($existingDoc->file_path) }}" target="_blank" class="btn btn-xs btn-link p-0 text-decoration-none small">
                                                                <i class="bi bi-eye"></i> View Current
                                                            </a>
                                                        </div>
                                                    @endif
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
                </div>

                {{-- Custom Fields Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-puzzle me-2 text-primary"></i>Custom Fields</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomField()">
                            <i class="bi bi-plus-circle me-1"></i> Add Field
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="customFieldsContainer">
                            @if($employee->custom_fields)
                                @foreach($employee->custom_fields as $key => $value)
                                    @if($key !== 'payroll_company' && $key !== 'insurance_provider' && $key !== 'insurance_policy_number' && $key !== 'insurance_start_date' && $key !== 'insurance_end_date')
                                    <div class="row g-2 mb-2 custom-field-row">
                                        <div class="col-md-5">
                                            <input type="text" name="dynamic_custom_fields[{{ $loop->index }}][name]" 
                                                   class="form-control form-control-sm" value="{{ $key }}" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="dynamic_custom_fields[{{ $loop->index }}][value]" 
                                                   class="form-control form-control-sm" value="{{ $value }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeCustomField(this)">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="text-muted small mt-2">
                            <i class="bi bi-info-circle-fill me-1"></i> Add custom fields as per your requirement.
                        </div>
                    </div>
                </div>

                {{-- Submit Actions --}}
                <div class="d-flex justify-content-end gap-3 mb-4">
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-check-lg me-1"></i> Update Employee
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);
    }
    .tracking-wide {
        letter-spacing: 2px;
    }
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    .card-header {
        border-radius: 1rem 1rem 0 0 !important;
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
</style>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

let customFieldIndex = {{ count(array_filter($employee->custom_fields ?? [], function($key) { return $key !== 'payroll_company' && $key !== 'insurance_provider' && $key !== 'insurance_policy_number' && $key !== 'insurance_start_date' && $key !== 'insurance_end_date'; }, ARRAY_FILTER_USE_KEY)) }};

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

// Update avatar on name change
document.querySelectorAll('input[name="first_name"], input[name="last_name"]').forEach(input => {
    input.addEventListener('input', function() {
        const first = document.getElementById('first_name')?.value || '{{ $employee->first_name }}';
        const last = document.querySelector('input[name="last_name"]')?.value || '{{ $employee->last_name }}';
        document.getElementById('preview').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(first + '+' + last)}&size=150&background=2B5797&color=fff&rounded=true&bold=true`;
    });
});

// Department -> Designation dependent dropdown
document.addEventListener('DOMContentLoaded', () => {
    const departmentSelect = document.getElementById('departmentSelect');
    const designationSelect = document.getElementById('designationSelect');
    if (!departmentSelect || !designationSelect) return;

    const selectedDesignationId = {{ old('designation_id', $employee->designation_id) ?: 'null' }};

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
