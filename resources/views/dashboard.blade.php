@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <!-- KPI Cards -->
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['total_employees']) }}</div>
                    <div class="text-muted small">Active Employees</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['payroll_processed']) }}</div>
                    <div class="text-muted small">Payroll Processed</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['payroll_pending']) }}</div>
                    <div class="text-muted small">Pending Payroll</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['monthly_net'], 0) }}</div>
                    <div class="text-muted small">Net Payroll ({{ now()->format('M') }})</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Payroll Trend -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Payroll Trend (6 Months)</span>
            </div>
            <div class="card-body">
                <canvas id="payrollChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Advances Summary -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <i class="bi bi-cash-coin me-2 text-warning"></i>Advance Summary
            </div>
            <div class="card-body">
                <div class="text-center py-3">
                    <div class="fs-2 fw-bold text-warning">
                        {{ number_format($stats['active_advances'], 0) }}
                    </div>
                    <div class="text-muted">Total Pending Advances</div>
                </div>
                <a href="{{ route('advances.index') }}" class="btn btn-outline-warning w-100">
                    View All Advances
                </a>
            </div>
        </div>
    </div>

    <!-- Document Expiry Alerts -->
    @if($expiringDocs->count())
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Document Expiry Alerts (Next 30 Days)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Document</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringDocs as $doc)
                            <tr>
                                <td>
                                    <a href="{{ route('employees.show', $doc->employee) }}">
                                        {{ $doc->employee->full_name }}
                                    </a>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</td>
                                <td>{{ $doc->expiry_date->format('d M Y') }}</td>
                                <td>
                                    @php $days = now()->diffInDays($doc->expiry_date, false) @endphp
                                    <span class="badge {{ $days <= 7 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $days }} days
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Payroll -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Payroll Activity</span>
                <a href="{{ route('payroll.history') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Month</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayroll as $record)
                            <tr>
                                <td>{{ $record->employee->full_name }}</td>
                                <td>{{ $record->month_label }}</td>
                                <td>{{ number_format($record->net_salary, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $record->status }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('payroll.slip', $record) }}" class="btn btn-xs btn-outline-secondary btn-sm">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No payroll records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('payrollChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($trend->pluck('month')),
        datasets: [{
            label: 'Net Payroll',
            data: @json($trend->pluck('total')),
            backgroundColor: 'rgba(43,87,151,0.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
