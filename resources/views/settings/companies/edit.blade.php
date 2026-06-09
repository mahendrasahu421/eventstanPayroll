{{-- resources/views/companies/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Company
        </h2>
        <div class="text-muted">Update company information</div>
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
                    <i class="bi bi-building me-2"></i>Company: {{ $company->company_name }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="company_name" class="form-label fw-semibold">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                id="company_name" name="company_name"
                                value="{{ old('company_name', $company->company_name) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="company_code" class="form-label fw-semibold">
                                Company Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('company_code') is-invalid @enderror"
                                id="company_code" name="company_code"
                                value="{{ old('company_code', $company->company_code) }}"
                                inputmode="numeric" pattern="\d{13}" maxlength="13" required>
                            @error('company_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="company_email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Company Email
                            </label>
                            <input type="email" class="form-control @error('company_email') is-invalid @enderror"
                                id="company_email" name="company_email"
                                value="{{ old('company_email', $company->company_email) }}">
                            @error('company_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="company_phone" class="form-label fw-semibold">
                                <i class="bi bi-telephone me-1"></i>Company Phone
                            </label>
                            <input type="text" class="form-control @error('company_phone') is-invalid @enderror"
                                id="company_phone" name="company_phone"
                                value="{{ old('company_phone', $company->company_phone) }}">
                            @error('company_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="currency" class="form-label fw-semibold">
                                <i class="bi bi-currency-exchange me-1"></i>Currency Code
                            </label>
                            <input type="text" class="form-control @error('currency') is-invalid @enderror"
                                id="currency" name="currency" value="{{ old('currency', $company->currency) }}"
                                maxlength="3">
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="currency_symbol" class="form-label fw-semibold">
                                <i class="bi bi-cash me-1"></i>Currency Symbol
                            </label>
                            <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror"
                                id="currency_symbol" name="currency_symbol"
                                value="{{ old('currency_symbol', $company->currency_symbol) }}" maxlength="5">
                            @error('currency_symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="working_days_per_month" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check me-1"></i>Working Days / Month
                            </label>
                            <input type="number" class="form-control @error('working_days_per_month') is-invalid @enderror"
                                id="working_days_per_month" name="working_days_per_month"
                                value="{{ old('working_days_per_month', $company->working_days_per_month) }}"
                                min="1" max="31">
                            @error('working_days_per_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="company_address" class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Company Address
                            </label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                id="company_address" name="company_address" rows="3">{{ old('company_address', $company->company_address) }}</textarea>
                            @error('company_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-image me-1"></i>Company Logo
                            </label>
                            
                            @if ($company->logo_url)
                                <div class="mb-3">
                                    <img src="{{ $company->logo_url }}" alt="Current Logo" style="max-height: 80px;">
                                    <p class="text-muted small mt-1">Current logo</p>
                                </div>
                            @endif
                            
                            <div class="upload-zone mb-2" id="logoPreviewZone"
                                onclick="document.getElementById('logoInput').click()" 
                                style="border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; cursor: pointer; text-align: center;">
                                <div>
                                    <i class="bi bi-cloud-upload" style="font-size: 2rem;"></i>
                                    <div class="fw-semibold">Click to upload new logo</div>
                                    <div class="text-muted small">PNG, JPG, GIF up to 2MB</div>
                                </div>
                            </div>
                            
                            <input type="file" class="d-none" id="logoInput" name="logo" accept="image/*" onchange="previewLogo(this)">
                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                    {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="bi bi-check-circle me-1"></i>Active Company
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Company
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
                    <i class="bi bi-info-circle me-1"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Company ID</small>
                    <div class="fw-semibold">#{{ $company->id }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Created at</small>
                    <div class="fw-semibold">{{ $company->created_at->format('d/m/y, h:i A') }}</div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Last updated</small>
                    <div class="fw-semibold">{{ $company->updated_at->format('d/m/y, h:i A') }}</div>
                </div>
                
                <hr>
                
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
            <div>
                <img src="${e.target.result}" alt="Preview" style="max-height: 80px;">
                <div class="fw-semibold text-success mt-2">Logo Selected</div>
                <div class="text-muted small">Click to change</div>
            </div>
        `;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
@endsection
