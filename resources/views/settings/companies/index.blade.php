{{-- resources/views/companies/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Companies')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-building text-primary me-2"></i>Companies
        </h2>
        <div class="text-muted">Manage all companies</div>
    </div>
    <div>
        <a href="{{ route('companies.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Company
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        {{-- <th width="70">Logo</th> --}}
                        <th>Company Code</th>
                        <th>Company Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Currency</th>
                        <th>Working Days</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        {{-- <td>
                            @if($company->logo_url || $company->logo)
                                <img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}"
                                     class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-building text-secondary"></i>
                                </div>
                            @endif
                        </td> --}}
                        <td>{{ $company->company_code ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ $company->company_name }}</strong>
                        </td>
                        <td>{{ $company->company_email ?? '—' }}</td>
                        <td>{{ $company->company_phone ?? '—' }}</td>
                        <td>{{ Str::limit($company->company_address, 30) ?? '—' }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $company->currency_symbol }} {{ $company->currency }}
                            </span>
                        </td>
                        <td class="text-center">{{ $company->working_days_per_month }}</td>
                        <td>
                            @if($company->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $company->created_at->format('d/m/y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
<a href="javascript:void(0)" class="btn btn-sm btn-info" title="View Documents" onclick="loadCompanyDocuments({{ $company->id }})">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="bi bi-building" style="font-size: 64px; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">No companies found</h5>
                            <p class="text-muted">Click "Add Company" to create your first company.</p>
                            <a href="{{ route('companies.create') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle"></i> Add Company
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $companies->links() }}
        </div>
    </div>
</div>

@push('scripts')
<!-- Company documents offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="companyDocumentsOffcanvas" aria-labelledby="companyDocumentsOffcanvasLabel" style="--bs-offcanvas-width: 560px;">
    <div class="offcanvas-header">
        <h6 class="offcanvas-title" id="companyDocumentsOffcanvasLabel">Company Documents</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="companyDocumentsBody">
        <div class="text-muted">Click company view to load documents.</div>
    </div>
</div>

<script>
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderDocumentPreview(doc) {
    if (!doc.file_url) {
        return '<div class="text-muted small">No file uploaded.</div>';
    }

    const fileUrl = escapeHtml(doc.file_url);
    const label = escapeHtml(doc.label || 'Company document');
    const extension = String(doc.file_extension || '').toLowerCase();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
        return `<img src="${fileUrl}" alt="${label}" class="img-fluid rounded border" style="max-height: 360px; width: 100%; object-fit: contain;">`;
    }

    if (extension === 'pdf') {
        return `<iframe src="${fileUrl}" title="${label}" class="border rounded w-100" style="height: 420px;"></iframe>`;
    }

    return '<div class="text-muted small">Preview not available for this file type.</div>';
}

async function loadCompanyDocuments(companyId) {
    const offcanvasEl = document.getElementById('companyDocumentsOffcanvas');
    const body = document.getElementById('companyDocumentsBody');

    if (!body) return;
    body.innerHTML = '<div class="text-muted">Loading...</div>';

    try {
        const res = await fetch(`/companies/${companyId}/documents`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        const docs = json.data || [];

        if (!docs.length) {
            body.innerHTML = '<div class="text-muted">No documents found.</div>';
        } else {
            body.innerHTML = docs.map(doc => {
                const label = escapeHtml(doc.label || '-');
                const expiry = doc.expiry_date ? `<span class="badge bg-light text-dark border">Expiry: ${escapeHtml(doc.expiry_date)}</span>` : '';
                const file = doc.file_url ? ` <a class="btn btn-sm btn-outline-primary" target="_blank" href="${escapeHtml(doc.file_url)}"><i class="bi bi-box-arrow-up-right"></i> Open</a>` : '';
                const preview = renderDocumentPreview(doc);

                return `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <div>
                                    <div class="fw-semibold">${label}</div>
                                </div>
                                <div>${expiry}</div>
                            </div>
                            <div class="mt-3">${preview}</div>
                            <div class="mt-2">${file}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        bsOffcanvas.show();
    } catch (e) {
        body.innerHTML = '<div class="text-danger">Failed to load documents.</div>';
    }
}

function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete company "${name}"? This action cannot be undone.`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}
</script>
@endpush
@endsection

