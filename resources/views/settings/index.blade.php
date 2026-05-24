@extends('layouts.app')

@section('title', 'Company Master')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">Company Master</h2>
        <div class="text-muted">(Single company setup)</div>
    </div>
</div>


<div class="row g-4">

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Company Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $settings->company_name ?? '') }}" placeholder="Company Name" required>
                                <label for="company_name">Company Name</label>
                            </div>
                            @error('company_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control @error('company_email') is-invalid @enderror" id="company_email" name="company_email" value="{{ old('company_email', $settings->company_email ?? '') }}" placeholder="Company Email">
                                <label for="company_email">Company Email</label>
                            </div>
                            @error('company_email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror" id="company_phone" name="company_phone" value="{{ old('company_phone', $settings->company_phone ?? '') }}" placeholder="Company Phone">
                                <label for="company_phone">Company Phone</label>
                            </div>
                            @error('company_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" value="{{ old('currency', $settings->currency ?? 'AED') }}" placeholder="Currency Code" required>
                                <label for="currency">Currency Code</label>
                            </div>
                            @error('currency')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol ?? 'د.إ') }}" placeholder="Currency Symbol" required>
                                <label for="currency_symbol">Currency Symbol</label>
                            </div>
                            @error('currency_symbol')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control @error('working_days_per_month') is-invalid @enderror" id="working_days_per_month" name="working_days_per_month" value="{{ old('working_days_per_month', $settings->working_days_per_month ?? 26) }}" placeholder="Working Days / Month" required min="1" max="31">
                                <label for="working_days_per_month">Working Days / Month</label>
                            </div>
                            @error('working_days_per_month')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address" name="company_address" style="height: 100px" placeholder="Company Address">{{ old('company_address', $settings->company_address ?? '') }}</textarea>
                                <label for="company_address">Company Address</label>
                            </div>
                            @error('company_address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="upload-zone mb-2" id="logoPreviewZone" onclick="document.getElementById('logoInput').click()">
                                <div class="d-flex align-items-center justify-content-center gap-3" style="min-height: 90px;">
                                    <div>
                                        <i class="bi bi-cloud-upload" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">Upload Company Logo</div>
                                        <div class="text-muted small">PNG/JPG up to 2MB</div>
                                    </div>
                                </div>
                            </div>

                            <input type="file" class="form-control @error('logo') is-invalid @enderror d-none" id="logoInput" name="logo" accept="image/*" onchange="previewLogo(this)" />

                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="mt-3">
                                @if(!empty($settings->logo))
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $settings->getLogoUrlAttribute() }}" alt="Company Logo" style="height: 70px; width: 70px; object-fit: contain; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;" />
                                        <div class="text-muted small">Current logo</div>
                                    </div>
                                @else
                                    <div class="text-muted small">No logo uploaded yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Company
                        </button>
                        <a href="{{ route('settings') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Preview</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Company</div>
                    <div class="fw-semibold">{{ $settings->company_name ?? '—' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Currency</div>
                    <div class="fw-semibold">{{ $settings->currency_symbol ?? 'AED' }} ({{ $settings->currency ?? 'AED' }})</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted small">Working Days / Month</div>
                    <div class="fw-semibold">{{ $settings->working_days_per_month ?? 26 }}</div>
                </div>

                <div class="mt-4">
                    <div class="text-muted small mb-1">How it affects payroll</div>
                    <ul class="mb-0 text-muted small">
                        <li>Salary slip currency symbol</li>
                        <li>Payroll calculations (working days)</li>
                    </ul>
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
                <div class="d-flex align-items-center justify-content-center gap-3" style="min-height: 90px;">
                    <img src="${e.target.result}" alt="Logo Preview" style="height: 70px; width: 70px; object-fit: contain; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;" />
                    <div>
                        <div class="fw-semibold">Logo Selected</div>
                        <div class="text-muted small">Click to change</div>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
</script>
@endpush
@endsection

