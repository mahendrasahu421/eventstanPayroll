{{-- resources/views/companies/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Company Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-building text-primary me-2"></i>Company Details
        </h2>
        <div class="text-muted">View and manage company information</div>
    </div>
    <div>
        <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit Company
        </a>
        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                @if($company->logo_url)
                    <div class="mb-3">
                        <img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}" 
                             class="img-fluid rounded" style="max-height: 150px; width: auto; border: 1px solid #e5e7eb; padding: 10px;">
                    </div>
                @else
                    <div class="bg-light p-4 rounded mb-3">
                        <i class="bi bi-building" style="font-size: 80px; color: #6c757d;"></i>
                    </div>
                @endif
                <h4 class="mb-1">{{ $company->company_name }}</h4>
                <p class="text-muted small mb-3">Company ID: #{{ $company->id }}</p>
                
                <div class="mt-2">
                    @if($company->is_active)
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i> Active
                        </span>
                    @else
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-x-circle me-1"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('companies.edit', $company) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-pencil-square me-2"></i> Edit Company
                    </a>
                    <button type="button" class="list-group-item list-group-item-action text-danger" 
                            onclick="confirmDelete({{ $company->id }}, '{{ $company->company_name }}')">
                        <i class="bi bi-trash me-2"></i> Delete Company
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Company Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Company Name</small>
                            <div class="fw-semibold fs-5">{{ $company->company_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Company Email</small>
                            <div class="fw-semibold">
                                @if($company->company_email)
                                    <a href="mailto:{{ $company->company_email }}">{{ $company->company_email }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Company Phone</small>
                            <div class="fw-semibold">
                                @if($company->company_phone)
                                    <a href="tel:{{ $company->company_phone }}">{{ $company->company_phone }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Currency Settings</small>
                            <div class="fw-semibold">
                                <span class="badge bg-info">{{ $company->currency_symbol }}</span>
                                {{ $company->currency }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Working Days / Month</small>
                            <div class="fw-semibold">
                                <i class="bi bi-calendar-week me-1"></i>
                                {{ $company->working_days_per_month }} days
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Company Address</small>
                            <div class="fw-semibold">
                                @if($company->company_address)
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $company->company_address }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">Payroll Settings</small>
                            @if($company->payroll_settings)
                                <div class="mt-2">
                                    <pre class="bg-light p-3 rounded" style="font-size: 12px; overflow-x: auto; margin-bottom: 0;">{{ json_encode($company->payroll_settings, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @else
                                <div class="text-muted">No payroll settings configured</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-clock-history me-1"></i>Created At
                            </small>
                            <div class="fw-semibold">{{ $company->created_at->format('d/m/y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-bottom pb-2">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-pencil-square me-1"></i>Last Updated
                            </small>
                            <div class="fw-semibold">{{ $company->updated_at->format('d/m/y, h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $company->id }}" action="{{ route('companies.destroy', $company) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete company "${name}"? This action cannot be undone.`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}
</script>
@endpush
@endsection