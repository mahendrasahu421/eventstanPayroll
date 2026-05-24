@extends('layouts.app')

@section('title', 'Payroll History')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-clock-history me-2"></i>Payroll History</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('payroll.process') }}" class="btn btn-primary me-2"><i class="bi bi-calculator"></i> Process</a>
        <a href="{{ route('payroll.bulk') }}" class="btn btn-warning"><i class="bi bi-lightning"></i> Bulk</a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><strong>{{ $records->total() }}</strong> records found</span>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
            <select name="employee" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option {{ request('employee') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="processed">Processed</option>
                <option value="approved">Approved</option>
                <option value="paid">Paid</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="payrollTable">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Month</th>
                    <th>Gross</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>{{ $record->employee->full_name }}<br><small>{{ $record->employee->employee_code }}</small></td>
                    <td>{{ \Carbon\Carbon::parse($record->payroll_month)->format('M Y') }}</td>
                    <td><strong>{{ number_format($record->gross_salary) }}</strong></td>
                    <td>{{ number_format($record->total_deductions) }}</td>
                    <td><span class="fs-5 fw-bold text-success">{{ number_format($record->net_salary) }}</span></td>
                    <td>
                        @switch($record->status)
                            @case('draft')<span class="badge badge-draft">Draft</span>
                            @case('processed')<span class="badge badge-processed">Processed</span>
                            @case('approved')<span class="badge badge-approved">Approved</span>
                            @case('paid')<span class="badge badge-paid">Paid</span>
                            @default<span class="badge bg-secondary">Unknown</span>
                        @endswitch
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('payroll.slip', $record) }}" class="btn btn-outline-primary" title="Salary Slip"><i class="bi bi-file-earmark-pdf"></i></a>
                            <form method="POST" action="{{ route('payroll.status', $record) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="{{ $record->status == 'paid' ? 'processed' : 'paid' }}">
                                <button class="btn btn-sm {{ $record->status == 'paid' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    <i class="{{ $record->status == 'paid' ? 'bi-arrow-counterclockwise' : 'bi-check-circle' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5">No payroll records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $records->appends(request()->query())->links() }}

@push('scripts')
<script>
$(() => {
    $('#payrollTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [{ targets: [5,6], orderable: false }]
    });
});
</script>
@endpush

@endsection

