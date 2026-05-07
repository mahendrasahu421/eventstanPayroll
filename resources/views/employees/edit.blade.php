@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-pencil-square fs-2 text-warning"></i>
    <h2>Edit Employee: {{ $employee->full_name }}</h2>
</div>

<form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" id="employeeForm">
    @csrf @method('PUT')

    {{-- Photo Upload --}}
    <div class="row mb-4">
        <div class="col-md-3 text-center">
            <div class="position-relative">
                @if($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" id="preview" class="rounded-circle border border-3 border-white shadow-sm" style="width:150px;height:150px;object-fit:cover;">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ $employee->first_name }}+{{ $employee->last_name }}&size=150&background=2B5797&color=fff" id="preview" class="rounded-circle border border-3 border-white shadow-sm" style="width:150px;height:150px;object-fit:cover;">
                @endif
                <label for="photo" class="photo-upload position-absolute bottom-0 end-0">
                    <i class="bi bi-camera-fill text-white bg-primary rounded-circle p-2"></i>
                    <input type="file" id="photo" name="photo" class="d-none" accept="image/*" onchange="previewPhoto(this)">
                </label>
            </div>
            @error('photo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Employee ID --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label fw-bold">Employee ID</label>
            <input type="text" class="form-control bg-light" value="{{ $employee->employee_code }}" readonly>
        </div>
    </div>

    {{-- Accordion Sections --}}
    <div class="accordion" id="employeeSections">

        {{-- Personal Details --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#personalDetails">
                    🧑‍💼 Personal Details
                </button>
            </h2>
            <div id="personalDetails" class="accordion-collapse collapse show" data-bs-parent="#employeeSections">
                <div class="accordion-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->last_name) }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Designation *</label>
                            <select name="designation_id" id="designationSelect" class="form-select @error('designation_id') is-invalid @enderror" required>
                                <option value="">Select Designation</option>
                                @foreach($designations as $des)
                                    <option value="{{ $des->id }}" data-department-id="{{ $des->department_id ?? '' }}" {{ old('designation_id', $employee->designation_id) == $des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department *</label>
                            <select name="department_id" id="departmentSelect" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <select name="country_id" class="form-select">
                                <option value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id', $employee->country_id) == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">WPS Number</label>
                            <input type="text" name="wps_personal_number" class="form-control" value="{{ old('wps_personal_number', $employee->wps_personal_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Join Date *</label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $employee->joining_date) }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Payroll Company</label>
                            <div class="form-control" style="background:#f8f9fa;">{{ $employee->custom_fields['payroll_company'] ?? '' }}</div>
                            <input type="hidden" name="custom_fields[payroll_company]" value="{{ old('custom_fields.payroll_company', $employee->custom_fields['payroll_company'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Salary & Compensation (load from salaryStructure) --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#salary">
                    💰 Salary & Compensation
                </button>
            </h2>
            <div id="salary" class="accordion-collapse collapse" data-bs-parent="#employeeSections">
                <div class="accordion-body">
                    @if($employee->salaryStructure)
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Basic Salary *</label>
                                <input type="number" name="basic_salary" step="0.01" class="form-control" value="{{ old('basic_salary', $employee->salaryStructure->basic_salary) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Overtime Hourly Rate</label>
                                <input type="number" name="overtime_rate_per_hour" step="0.01" class="form-control" value="{{ old('overtime_rate_per_hour', $employee->salaryStructure->overtime_rate_per_hour) }}" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">WPS Salary / 1st Transfer</label>
                                <input type="number" name="wps_first_transfer_amount" step="0.01" class="form-control" value="{{ old('wps_first_transfer_amount', $employee->salaryStructure->wps_first_transfer_amount) }}" min="0">
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No salary structure found. <a href="{{ route('employees.salary', $employee) }}">Set up salary first</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Documents (display existing, allow add new) --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#documents">
                    📄 Documents ({{ $employee->documents->count() }})
                </button>
            </h2>
            <div id="documents" class="accordion-collapse collapse" data-bs-parent="#employeeSections">
                <div class="accordion-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead><tr><th>Type</th><th>Number</th><th>Expiry</th><th>Actions</th></tr></thead>
                            <tbody>
                                @foreach($employee->documents as $doc)
                                    <tr>
                                        <td>{{ ucfirst($doc->document_type) }}</td>
                                        <td>{{ $doc->document_number ?? '-' }}</td>
                                        <td>{{ $doc->expiry_date?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            @if($doc->file_path)
                                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <h6 class="mb-2">Add New Document</h6>
                    {{-- Simplified new document add for edit --}}
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="new_document[type]" class="form-select form-select-sm">
                                <option value="">Select Type</option>
                                <option value="passport">Passport</option>
                                <option value="emirates_id">Emirates ID</option>
                                <option value="labour_card">Labour Card</option>
                                <option value="driving_license">Driving License</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="new_document[number]" class="form-control form-control-sm" placeholder="Number">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="new_document[expiry_date]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <input type="file" name="new_document[file]" class="form-control form-control-sm" accept="image/*,application/pdf">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Custom Fields --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#customFields">
                    ⚙️ Custom Fields
                </button>
            </h2>
            <div id="customFields" class="accordion-collapse collapse" data-bs-parent="#employeeSections">
                <div class="accordion-body">
                    <div id="customFieldsContainer">
                        @if($employee->custom_fields)
                            @foreach($employee->custom_fields as $key => $value)
                                <div class="row g-2 mb-2 custom-field-row">
                                    <div class="col-md-5">
                                        <input type="text" name="dynamic_custom_fields[{{ $loop->index }}][name]" class="form-control form-control-sm" value="{{ $key }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="dynamic_custom_fields[{{ $loop->index }}][value]" class="form-control form-control-sm" value="{{ $value }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeCustomField(this)">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addCustomField()">
                        <i class="bi bi-plus"></i> Add Custom Field
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Update Employee
        </button>
    </div>
</form>

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

let customFieldIndex = {{ count($employee->custom_fields ?? []) }};
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
        document.getElementById('preview').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(first + '+' + last)}&size=150&background=2B5797&color=fff`;
    });
});

// Department -> Designation dependent dropdown
document.addEventListener('DOMContentLoaded', () => {
    const departmentSelect = document.getElementById('departmentSelect');
    const designationSelect = document.getElementById('designationSelect');
    if (!departmentSelect || !designationSelect) return;

    const selectedDesignationId = {{ old('designation_id', $employee->designation_id) ? old('designation_id', $employee->designation_id) : 'null' }};

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



