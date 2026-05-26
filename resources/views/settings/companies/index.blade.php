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
                        <th width="70">Logo</th>
                        <th>Company Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Currency</th>
                        <th>Working Days</th>
                        <th>Overtime Rate</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>
                            @if($company->logo_url)
                                <img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}" 
                                     class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-building text-secondary"></i>
                                </div>
                            @endif
                        </td>
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
                            @if($company->overtime_rate)
                                <span class="badge bg-warning">
                                    {{ $company->currency_symbol }} {{ number_format($company->overtime_rate, 2) }}/hr
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($company->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $company->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                               
                            </div>
                            {{-- <form id="delete-form-{{ $company->id }}" action="{{ route('companies.destroy', $company) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form> --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-5">
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
<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete company "${name}"? This action cannot be undone.`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}
</script>
@endpush
@endsection