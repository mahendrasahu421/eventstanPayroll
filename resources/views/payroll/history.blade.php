{{-- resources/views/payroll/history.blade.php --}}
@extends('layouts.app')

@section('title', 'Payroll History')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                <i class="bi bi-clock-history fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Payroll History</h1>
                <p class="text-muted mb-0">View and manage all payroll records</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.process') }}" class="btn btn-primary">
                <i class="bi bi-calculator me-1"></i> Process Payroll
            </a>
            <a href="{{ route('payroll.bulk') }}" class="btn btn-outline-warning">
                <i class="bi bi-lightning me-1"></i> Bulk Payroll
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Records</small>
                            <h2 class="mb-0 fw-bold">{{ $records->total() }}</h2>
                        </div>
                        <i class="bi bi-database fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Paid</small>
                            <h2 class="mb-0 fw-bold">{{ $records->where('status', 'paid')->count() }}</h2>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-warning text-white" style="background: linear-gradient(135deg, #D97706 0%, #B45309 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Total Amount</small>
                            <h2 class="mb-0 fw-bold">{{ number_format($records->sum('net_salary'), 0) }}</h2>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="opacity-75">Avg Net Salary</small>
                            <h2 class="mb-0 fw-bold">  {{ number_format($records->avg('net_salary') ?? 0, 0) }}</h2>
                        </div>
                        <i class="bi bi-graph-up fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2 text-primary"></i>Filter Payroll Records</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Month</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-calendar-month"></i></span>
                        <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Employee</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <select name="employee" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <a href="{{ route('payroll.history') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-repeat me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Payroll Records Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-list-check me-2 text-primary"></i>Payroll Records
                <span class="badge bg-primary ms-2">{{ $records->total() }} Records</span>
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf me-2"></i>Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel me-2"></i>Export as Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-printer me-2"></i>Print</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="payrollTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Employee Details</th>
                            <th>Payroll Month</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Processed Date</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $record)
                        <tr>
                            <td>{{ $records->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($record->employee && $record->employee->photo)
                                        <img src="{{ Storage::url($record->employee->photo) }}" class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:35px;height:35px;">
                                            <i class="bi bi-person text-secondary"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $record->employee->full_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $record->employee->employee_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($record->payroll_month)->format('F Y') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($record->payroll_month)->format('M Y') }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-primary">{{ number_format($record->gross_salary, 2) }}</span>
                            </td>
                            <td>
                                <span class="text-danger">{{ number_format($record->total_deductions, 2) }}</span>
                            </td>
                            <td>
                                <span class="fs-5 fw-bold text-success">{{ number_format($record->net_salary, 2) }}</span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'processed' => 'info',
                                        'approved' => 'warning',
                                        'paid' => 'success'
                                    ];
                                    $statusIcons = [
                                        'draft' => 'bi-pencil',
                                        'processed' => 'bi-gear',
                                        'approved' => 'bi-check-circle',
                                        'paid' => 'bi-cash-stack'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }} px-3 py-2">
                                    <i class="{{ $statusIcons[$record->status] ?? 'bi-clock' }} me-1"></i>
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $record->processed_at ? \Carbon\Carbon::parse($record->processed_at)->format('d M Y') : '-' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('payroll.slip', $record) }}" class="btn btn-outline-primary" title="Salary Slip">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    @if($record->status != 'paid')
                                        <button type="button" class="btn btn-outline-success" title="Mark as Paid" onclick="updateStatus({{ $record->id }}, 'paid')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-warning" title="Mark as Draft" onclick="updateStatus({{ $record->id }}, 'draft')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteRecord({{ $record->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">No payroll records found</h6>
                                    <p class="text-muted small">Click "Process Payroll" to create your first payroll record</p>
                                    <a href="{{ route('payroll.process') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-calculator me-1"></i> Process Payroll
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
                    </small>
                </div>
                <div>
                    {{ $records->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        display: none;
    }
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    .badge {
        font-weight: 500;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #0891B2 0%, #0E7490 100%);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateStatus(id, status) {
        let title = status === 'paid' ? 'Mark as Paid' : 'Mark as Draft';
        let text = status === 'paid' ? 'This will mark the payroll as paid and finalize it.' : 'This will change the status back to draft.';
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'paid' ? '#28a745' : '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/payroll/' + id + '/status',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Could not update status'
                        });
                    }
                });
            }
        });
    }
    
    function deleteRecord(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/payroll/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Could not delete record'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush