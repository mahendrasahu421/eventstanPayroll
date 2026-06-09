@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid px-4">
        {{-- Welcome Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-speedometer2 fs-2 text-primary"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Dashboard</h1>
                    <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
                </div>
            </div>
            <div class="text-end">
                <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
            </div>
        </div>

        {{-- KPI Cards Row 1 --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1 text-uppercase">Total Employees</p>
                                <h2 class="fw-bold mb-0">{{ number_format($stats['total_employees']) }}</h2>
                                <small class="text-success">
                                    <i class="bi bi-arrow-up-short"></i> +12% from last month
                                </small>
                            </div>
                            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people fs-3 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1 text-uppercase">Payroll Processed</p>
                                <h2 class="fw-bold mb-0">{{ number_format($stats['payroll_processed']) }}</h2>
                                <small class="text-success">
                                    <i class="bi bi-check-circle-fill"></i> This month
                                </small>
                            </div>
                            <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                <i class="bi bi-check2-circle fs-3 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1 text-uppercase">Pending Payroll</p>
                                <h2 class="fw-bold mb-0">{{ number_format($stats['payroll_pending']) }}</h2>
                                <small class="text-warning">
                                    <i class="bi bi-clock"></i> Awaiting processing
                                </small>
                            </div>
                            <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-clock-history fs-3 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small mb-1 text-uppercase">Monthly Net Payroll</p>
                                <h2 class="fw-bold mb-0">{{ number_format($stats['monthly_net'], 0) }}</h2>
                                <small class="text-info">
                                    <i class="bi bi-cash-stack"></i> {{ now()->format('F Y') }}
                                </small>
                            </div>
                            <div class="rounded-3 bg-info bg-opacity-10 p-3">
                                <i class="bi bi-cash-stack fs-3 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards Row 2 - Additional Metrics --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Total Departments</p>
                                <h4 class="fw-bold mb-0">{{ $stats['total_departments'] ?? 0 }}</h4>
                            </div>
                            <i class="bi bi-diagram-3 fs-3 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Active Advances</p>
                                <h4 class="fw-bold mb-0">{{ number_format($stats['active_advances'], 0) }}</h4>
                            </div>
                            <i class="bi bi-cash-coin fs-3 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <div class="row g-4">
            {{-- Payroll Trend Chart --}}
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-graph-up me-2 text-primary"></i>Payroll Trend
                                </h5>
                                <p class="text-muted small mb-0">Last 6 months overview</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="bi bi-calendar me-1"></i> 6 Months
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">3 Months</a></li>
                                    <li><a class="dropdown-item" href="#">6 Months</a></li>
                                    <li><a class="dropdown-item" href="#">12 Months</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="payrollChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            {{-- Quick Actions & Stats --}}
            <div class="col-xl-5">
                <div class="row g-4">
                    {{-- Quick Actions --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pt-4">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-lightning me-2 text-warning"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <a href="{{ route('employees.create') }}" class="text-decoration-none">
                                            <div class="bg-light rounded-3 p-3 text-center hover-bg transition">
                                                <i class="bi bi-person-plus fs-2 text-primary mb-2 d-block"></i>
                                                <span class="fw-semibold">Add Employee</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('payroll.process') }}" class="text-decoration-none">
                                            <div class="bg-light rounded-3 p-3 text-center hover-bg transition">
                                                <i class="bi bi-calculator fs-2 text-success mb-2 d-block"></i>
                                                <span class="fw-semibold">Process Payroll</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('advances.index') }}" class="text-decoration-none">
                                            <div class="bg-light rounded-3 p-3 text-center hover-bg transition">
                                                <i class="bi bi-cash-coin fs-2 text-warning mb-2 d-block"></i>
                                                <span class="fw-semibold">Manage Advances</span>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Document Expiry Summary --}}
                    @if ($expiringDocs->count())
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent border-0 pt-4">
                                    <h5 class="mb-0 fw-bold">
                                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Document Expiry Alerts
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach ($expiringDocs->take(3) as $doc)
                                            <a href="{{ route('employees.show', $doc->employee_id) }}"
                                                class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-semibold">{{ $doc->employee->full_name }}</div>
                                                        <small
                                                            class="text-muted">{{ ucwords(str_replace('_', ' ', $doc->document_type)) }}</small>
                                                    </div>
                                                    @php
                                                        $days = floor(now()->diffInDays($doc->expiry_date, false));
                                                    @endphp

                                                    <span class="badge {{ $days <= 7 ? 'bg-danger' : 'bg-warning' }}">
                                                        {{ $days }} Days
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                        @if ($expiringDocs->count() > 3)
                                            <div class="text-center p-3">
                                                <a href="{{ route('employee-documents') }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    View all {{ $expiringDocs->count() }} expiring documents
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Payroll Activity --}}
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div
                        class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history me-2 text-primary"></i>Recent Payroll Activity
                            </h5>
                            <p class="text-muted small mb-0">Latest payroll records</p>
                        </div>
                        <a href="{{ route('payroll.history') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Month</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPayroll as $record)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $record->employee->full_name }}</div>
                                                        <small
                                                            class="text-muted">{{ $record->employee->employee_code }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $record->employee->department?->name ?? '-' }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ \Carbon\Carbon::parse($record->payroll_month)->format('M Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold text-success">{{ number_format($record->net_salary, 2) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'draft' => 'secondary',
                                                        'processed' => 'info',
                                                        'approved' => 'warning',
                                                        'paid' => 'success',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }} px-3 py-2">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('payroll.slip', $record) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View Slip">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted mb-2 d-block"></i>
                                                <p class="text-muted mb-0">No payroll records found</p>
                                                <a href="{{ route('payroll.process') }}"
                                                    class="btn btn-sm btn-primary mt-2">
                                                    Process Payroll
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    @push('styles')
        <style>
            .transition {
                transition: all 0.3s ease;
            }

            .hover-shadow:hover {
                transform: translateY(-4px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
            }

            .hover-bg:hover {
                background-color: #e9ecef !important;
                transform: translateY(-2px);
            }

            .stat-card {
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                transform: translateY(-2px);
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('payrollChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($trend->pluck('month')),
                            datasets: [{
                                label: 'Net Payroll (AED)',
                                data: @json($trend->pluck('total')),
                                borderColor: '#2B5797',
                                backgroundColor: 'rgba(43,87,151,0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#2B5797',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'AED ' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'AED ' + value.toLocaleString();
                                        }
                                    },
                                    grid: {
                                        display: true,
                                        color: 'rgba(0,0,0,0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
