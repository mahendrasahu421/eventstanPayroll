{{-- resources/views/companies/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Company')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-plus-circle text-success me-2"></i>Add Company
        </h2>
        <div class="text-muted">Create a new company</div>
    </div>
    <div>
        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-building me-2"></i>Company Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <!-- Company Name -->
                        <div class="col-md-12">
                            <label for="company_name" class="form-label fw-semibold">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                id="company_name" name="company_name" value="{{ old('company_name') }}" 
                                placeholder="Enter company name" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company Email -->
                        <div class="col-md-6">
                            <label for="company_email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Company Email
                            </label>
                            <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                                id="company_email" name="company_email" value="{{ old('company_email') }}" 
                                placeholder="company@example.com">
                            @error('company_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company Phone -->
                        <div class="col-md-6">
                            <label for="company_phone" class="form-label fw-semibold">
                                <i class="bi bi-telephone me-1"></i>Company Phone
                            </label>
                            <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                id="company_phone" name="company_phone" value="{{ old('company_phone') }}" 
                                placeholder="+1 234 567 8900">
                            @error('company_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Currency Code -->
                        <div class="col-md-3">
                            <label for="currency" class="form-label fw-semibold">
                                <i class="bi bi-currency-exchange me-1"></i>Currency Code
                            </label>
                            <input type="text" class="form-control @error('currency') is-invalid @enderror"
                                id="currency" name="currency" value="{{ old('currency', 'AED') }}" 
                                maxlength="3" placeholder="AED">
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Currency Symbol -->
                        <div class="col-md-3">
                            <label for="currency_symbol" class="form-label fw-semibold">
                                <i class="bi bi-cash me-1"></i>Currency Symbol
                            </label>
                            <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror"
                                id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', 'د.إ') }}" 
                                maxlength="5" placeholder="د.إ">
                            @error('currency_symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Working Days -->
                        <div class="col-md-3">
                            <label for="working_days_per_month" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check me-1"></i>Working Days/Month
                            </label>
                            <input type="number" class="form-control @error('working_days_per_month') is-invalid @enderror"
                                id="working_days_per_month" name="working_days_per_month" 
                                value="{{ old('working_days_per_month', 26) }}" min="1" max="31">
                            @error('working_days_per_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Overtime Rate -->
                        <div class="col-md-3">
                            <label for="overtime_rate" class="form-label fw-semibold">
                                <i class="bi bi-clock-history me-1"></i>Overtime Rate
                            </label>
                            <input type="number" step="0.01" class="form-control @error('overtime_rate') is-invalid @enderror"
                                id="overtime_rate" name="overtime_rate" value="{{ old('overtime_rate') }}" 
                                placeholder="Enter amount">
                            @error('overtime_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Per hour overtime amount (e.g., 50, 75, 100)</small>
                        </div>

                        <!-- Company Address -->
                        <div class="col-12">
                            <label for="company_address" class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Company Address
                            </label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                id="company_address" name="company_address" rows="3" 
                                placeholder="Enter complete company address">{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Company Logo -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-image me-1"></i>Company Logo
                            </label>
                            <div class="upload-zone" id="logoPreviewZone"
                                onclick="document.getElementById('logoInput').click()" 
                                style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 30px; cursor: pointer; text-align: center;">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-cloud-upload" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    <div class="fw-semibold">Click to upload company logo</div>
                                    <div class="text-muted small">PNG, JPG, GIF up to 2MB</div>
                                </div>
                            </div>
                            <input type="file" class="d-none" id="logoInput" name="logo" accept="image/*" onchange="previewLogo(this)">
                            @error('logo')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="bi bi-check-circle me-1"></i>Active Company
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Company
                        </button>
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-1"></i>Information Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-primary">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Quick Tips:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Company Name is required</li>
                        <li>Currency affects all financial calculations</li>
                        <li>Working days used for daily rate calculation</li>
                        <li>Overtime Rate: Enter amount per hour (e.g., 50, 75, 100)</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6 class="fw-semibold">Example:</h6>
                    <div class="bg-light p-2 rounded small">
                        <strong>If overtime rate = 50</strong><br>
                        10 overtime hours = 500 currency extra
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    const zone = document.getElementById('logoPreviewZone');
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        zone.innerHTML = `
            <div class="d-flex flex-column align-items-center gap-2">
                <img src="${e.target.result}" alt="Logo Preview" style="max-height: 100px; width: auto; border-radius: 8px;">
                <div class="fw-semibold text-success">Logo Selected</div>
                <div class="text-muted small">Click to change</div>
            </div>
        `;
        zone.style.borderColor = '#28a745';
        zone.style.backgroundColor = '#f0fff4';
    };
    reader.readAsDataURL(input.files[0]);
}

// Hover effect for upload zone
const uploadZone = document.getElementById('logoPreviewZone');
uploadZone.addEventListener('mouseenter', function() {
    if (!document.getElementById('logoInput').files || !document.getElementById('logoInput').files[0]) {
        this.style.borderColor = '#0d6efd';
        this.style.backgroundColor = '#e7f1ff';
    }
});
uploadZone.addEventListener('mouseleave', function() {
    if (!document.getElementById('logoInput').files || !document.getElementById('logoInput').files[0]) {
        this.style.borderColor = '#dee2e6';
        this.style.backgroundColor = 'transparent';
    }
});
</script>
@endpush
@endsection