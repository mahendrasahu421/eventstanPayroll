@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body py-4">
                @if($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" class="rounded-circle mx-auto d-block mb-3 border border-4 border-white shadow-lg" style="width:120px;height:120px;object-fit:cover;">
                @else
                    <div class="rounded-circle mx-auto mb-3 bg-primary d-flex align-items-center justify-content-center text-white shadow-lg" style="width:120px;height:120px;font-size:2rem;font-weight:bold">
                        {{ substr($employee->first_name,0,1).strtoupper(substr($employee->last_name,0,1)) }}
                    </div>
                @endif
                <h4 class="mb-1">{{ $employee->full_name }}</h4>
                <p class="text-muted mb-1">{{ $employee->employee_code }}</p>
                @if($employee->status === 'active')
                    <span class="badge badge-active fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>Active</span>
                @else
                    <span class="badge badge-inactive fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">Profile Information</h3>
            <div>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-pencil"></i> Edit</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <strong>Department:</strong> <span class="ms-2">{{ $employee->department?->name ?? '-' }}</span><br>
                <strong>Designation:</strong> <span class="ms-2">{{ $employee->designation?->name ?? '-' }}</span><br>
                <strong>Email:</strong> {{ $employee->email ?? 'N/A' }}<br>
                <strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}<br>
                <strong>WPS Number:</strong> {{ $employee->wps_personal_number ?? 'N/A' }}<br>
                <strong>Nationality:</strong> {{ $employee->country?->name ?? ucfirst($employee->nationality ?? 'N/A') }}
                <br/>

            </div>
            <div class="col-md-6">
                <strong>Joining Date:</strong> {{ $employee->joining_date?->format('d M Y') ?? 'N/A' }}<br>
                @if($employee->custom_fields['payroll_company'] ?? false)
                    <strong>Payroll Company:</strong> {{ $employee->custom_fields['payroll_company'] }}<br>
                @endif
                @if($employee->custom_fields['insurance_provider'] ?? false)
                    <strong>Insurance Provider:</strong> {{ $employee->custom_fields['insurance_provider'] }}<br>
                    <strong>Policy #:</strong> {{ $employee->custom_fields['insurance_policy_number'] ?? 'N/A' }}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Salary Structure Card --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="bi bi-currency-dollar me-2"></i>Salary & Compensation</h5>
        @if($employee->salaryStructure)
            <div class="card">
                <div class="card-body">
                    <div class="row text-center text-md-start">
                        <div class="col-md-3 mb-3"><strong>Basic Salary</strong><br>{{ number_format($employee->salaryStructure->basic_salary) }}</div>
                        <div class="col-md-2 mb-3"><strong>Overtime Rate</strong><br>{{ number_format($employee->salaryStructure->overtime_rate_per_hour) }}/hr</div>
                        <div class="col-md-3 mb-3"><strong>WPS 1st Transfer</strong><br>{{ number_format($employee->salaryStructure->wps_first_transfer_amount) }}</div>
                        <div class="col-md-2 mb-3"><strong>Gross Salary</strong><br><span class="fs-5 fw-bold text-primary">{{ number_format($employee->salaryStructure->gross_salary) }}</span></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-8">
                            <h6 class="mb-2">Deductions</h6>
                            <div class="row">
                                <div class="col-md-3"><strong>Mess/Food:</strong> {{ number_format($employee->salaryStructure->food_deduction) }}</div>
                                <div class="col-md-3"><strong>Visa:</strong> {{ number_format($employee->salaryStructure->visa_deduction) }}</div>
                                <div class="col-md-3"><strong>Insurance:</strong> {{ number_format($employee->salaryStructure->insurance_deduction) }}</div>
                                <div class="col-md-3"><strong>Net Salary:</strong> <span class="fw-bold text-success">{{ number_format($employee->salaryStructure->gross_salary - ($employee->salaryStructure->food_deduction + $employee->salaryStructure->visa_deduction + $employee->salaryStructure->insurance_deduction)) }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('employees.salary', $employee) }}" class="btn btn-sm btn-outline-primary">Edit Salary</a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                No salary structure defined. <a href="{{ route('employees.salary', $employee) }}">Set up now</a>.
            </div>
        @endif
    </div>
</div>

{{-- Tabs for related data --}}
<div class="row">
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="employeeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">
                    Documents ({{ $employee->documents->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="advances-tab" data-bs-toggle="tab" data-bs-target="#advances" type="button">
                    Advances ({{ $employee->advances->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom" type="button">
                    Custom Fields ({{ count($employee->custom_fields ?? []) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payrolls-tab" data-bs-toggle="tab" data-bs-target="#payrolls" type="button">
                    Payroll Records
                </button>
            </li>
        </ul>

        <div class="tab-content" id="employeeTabContent">
{{-- Documents --}}
            <div class="tab-pane fade show active" id="documents" role="tabpanel">
                @if($employee->documents->count())
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Type</th><th>Number</th><th>Expiry</th><th>File</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($employee->documents as $doc)
                                    <tr>
                                        <td><strong>{{ ucfirst($doc->document_type) }}</strong></td>
                                        <td>{{ $doc->document_number ?? '-' }}</td>
                                        <td>{{ $doc->expiry_date?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            @if($doc->file_path)
                                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($doc->isExpiringSoon())
                                                <span class="badge bg-warning">Expiring Soon</span>
                                            @elseif($doc->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-success">Valid</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted"><i class="bi bi-file-earmark-text fs-1 mb-3"></i><br>No documents</div>
                @endif
            </div>


            {{-- Advances --}}
            <div class="tab-pane fade" id="advances" role="tabpanel">
                @if($employee->advances->count())
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>#</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($employee->advances->take(10) as $adv)
                                    <tr>
                                        <td><a href="{{ route('advances.show', $adv) }}">#{{ $adv->id }}</a></td>
                                        <td>{{ number_format($adv->amount) }}</td>
                                        <td>{{ $adv->advance_date->format('d M Y') }}</td>
                                        <td><span class="badge bg-info">{{ ucfirst($adv->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($employee->advances->count() > 10)
                            <small class="text-muted">Showing 10 of {{ $employee->advances->count() }} advances</small>
                        @endif
                    </div>
                @else
                    <div class="text-center py-5 text-muted"><i class="bi bi-cash-coin fs-1 mb-3"></i><br>No advances</div>
                @endif
            </div>

            {{-- Custom Fields --}}
            <div class="tab-pane fade" id="custom" role="tabpanel">
                @if($employee->custom_fields && count($employee->custom_fields))
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Field Name</th><th>Value</th></tr></thead>
                            <tbody>
                                @foreach($employee->custom_fields as $key => $value)
                                    <tr>
                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted"><i class="bi bi-gear fs-1 mb-3"></i><br>No custom fields</div>
                @endif
            </div>

            {{-- Payrolls --}}
            <div class="tab-pane fade" id="payrolls" role="tabpanel">
                @if($employee->payrollRecords->count())
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Month</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach($employee->payrollRecords->take(10) as $record)
                                    <tr>
                                        <td>{{ $record->payroll_month->format('M Y') }}</td>
                                        <td>{{ number_format($record->gross_salary) }}</td>
                                        <td>{{ number_format($record->total_deductions) }}</td>
                                        <td><strong>{{ number_format($record->net_salary) }}</strong></td>
                                        <td><span class="badge bg-success">{{ ucfirst($record->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted"><i class="bi bi-calendar-check fs-1 mb-3"></i><br>No payroll records</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <a href="{{ route('payroll.process') }}" class="btn btn-primary w-100 mb-2">Process Payroll</a>
        <a href="{{ route('payroll.history') }}" class="btn btn-outline-primary w-100 mb-2">Payroll History</a>
        <a href="{{ route('advances.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline-success w-100 mb-2">New Advance</a>
    </div>
</div>

@endsection

