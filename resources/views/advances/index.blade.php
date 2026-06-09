@extends('layouts.app')

@section('title', 'Advances')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">
            <i class="bi bi-cash-coin me-2"></i>
            Advances
        </h2>
        <div class="text-muted mt-1">
            <strong>{{ $advances->total() }}</strong> records
        </div>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('advances.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Advance
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>Search & Filters</strong>
        </div>

        <form method="GET" class="row g-2 mt-2" style="align-items: end;">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="" {{ request('status') === null ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="recovered" {{ request('status') === 'recovered' ? 'selected' : '' }}>Recovered</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Employee</label>
                <select name="employee" class="form-select form-select-sm">
                    <option value="" {{ request('employee') === null ? 'selected' : '' }}>All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string)request('employee') === (string)$employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name ?? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="advancesTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Amount</th>
                        <th>Pending</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $advance)
                        <tr>
                            <td><strong>#{{ $advance->id }}</strong></td>
                            <td>{{ 
                                $advance->advance_date
                                    ? date('d/m/y', strtotime($advance->advance_date))
                                    : ($advance->created_at ? $advance->created_at->format('d/m/y') : '-')
                            }}</td>
                            <td>
                                {{ $advance->employee?->full_name ?? trim(($advance->employee?->first_name ?? '') . ' ' . ($advance->employee?->last_name ?? '')) }}
                                <div class="text-muted" style="font-size: .85rem;">
                                    {{ $advance->employee?->employee_code ?? '' }}
                                </div>
                            </td>
                            <td>{{ number_format((float)($advance->amount ?? 0), 2) }}</td>
                            <td>{{ number_format((float)($advance->pending_amount ?? 0), 2) }}</td>
                            <td>
                                @if($advance->receipt_path)
                                    <a href="{{ route('advances.receipt', $advance) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-receipt"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $status = $advance->status;
                                @endphp
                                @if($status === 'approved')
                                    <span class="badge bg-success">{{ ucfirst($status) }}</span>
                                @elseif($status === 'recovered')
                                    <span class="badge bg-primary">{{ ucfirst($status) }}</span>
                                @elseif($status === 'cancelled')
                                    <span class="badge bg-secondary">{{ ucfirst($status) }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($status ?? 'pending') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('advances.show', $advance) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <form method="POST" action="{{ route('advances.destroy', $advance) }}" onsubmit="return confirm('Cancel this advance?');" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger" title="Cancel">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $advances->links() }}
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        if ($.fn.DataTable && $('#advancesTable tbody tr').length > 0) {
            if ($.fn.DataTable.isDataTable('#advancesTable')) {
                $('#advancesTable').DataTable().destroy();
            }

            $('#advancesTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ targets: -1, orderable: false }],
                destroy: true
            });
        }
    });
</script>
@endpush
@endsection