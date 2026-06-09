@extends('layouts.app')

@section('title', $employee->full_name)

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                <i class="bi bi-person-circle fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">{{ $employee->full_name }}</h1>
                <p class="text-muted mb-0">{{ $employee->employee_code }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.process') }}?employee_id={{ $employee->id }}" class="btn btn-primary">
                <i class="bi bi-calculator me-1"></i> Process Payroll
            </a>
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column - Profile & Quick Actions --}}
        <div class="col-lg-4">
            {{-- Profile Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    @if($employee->photo)
                        <img src="{{ Storage::url($employee->photo) }}" 
                             class="rounded-circle mx-auto d-block mb-3 border border-4 border-white shadow-lg" 
                             style="width: 140px; height: 140px; object-fit: cover;">
                    @else
                        <div class="rounded-circle mx-auto mb-3 bg-gradient-primary d-flex align-items-center justify-content-center text-white shadow-lg" 
                             style="width: 140px; height: 140px; font-size: 3rem; font-weight: bold; background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);">
                            {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h3 class="mb-1">{{ $employee->full_name }}</h3>
                    <p class="text-muted mb-2">{{ $employee->employee_code }}</p>
                    
                    @if($employee->status === 'active')
                        <span class="badge bg-success px-3 py-2 rounded-pill">
                            <i class="bi bi-check-circle me-1"></i> Active
                        </span>
                    @else
                        <span class="badge bg-secondary px-3 py-2 rounded-pill">
                            <i class="bi bi-x-circle me-1"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge me-2 text-primary"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('payroll.process') }}?employee_id={{ $employee->id }}" class="btn btn-primary">
                            <i class="bi bi-calculator me-2"></i>Process Payroll
                        </a>
                        <a href="{{ route('payroll.history') }}?employee={{ $employee->id }}" class="btn btn-outline-primary">
                            <i class="bi bi-clock-history me-2"></i>View Payroll History
                        </a>
                        <a href="{{ route('advances.create') }}?employee_id={{ $employee->id }}" class="btn btn-outline-success">
                            <i class="bi bi-cash-stack me-2"></i>Create Advance
                        </a>
                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            {{-- Contact Information Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-envelope-paper me-2 text-primary"></i>Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Email Address</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope me-2 text-primary"></i>
                            <span>{{ $employee->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Phone Number</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-telephone me-2 text-primary"></i>
                            <span>{{ $employee->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-muted small mb-1">Nationality</label>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-flag me-2 text-primary"></i>
                            <span>{{ $employee->country?->name ?? ucfirst($employee->nationality ?? 'N/A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Main Content --}}
        <div class="col-lg-8">
            {{-- Employment Information Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>Employment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Department</span>
                                    <span class="fw-semibold">{{ $employee->department?->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Designation</span>
                                    <span class="fw-semibold">{{ $employee->designation?->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Joining Date</span>
                                    <span class="fw-semibold">{{ $employee->joining_date?->format('d/m/y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">WPS Number</span>
                                    <span class="fw-semibold">{{ $employee->wps_personal_number ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Payroll Company</span>
                                    <span class="fw-semibold">{{ $employee->company?->company_name ?? $employee->company?->name ?? $companyName ?? 'Not configured' }}</span>
                                </div>
                                @if($employee->custom_fields['insurance_provider'] ?? false)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Insurance</span>
                                    <span class="fw-semibold">{{ $employee->custom_fields['insurance_provider'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary & Compensation Card --}}
            {{-- Includes installment-based recoveries breakdown for Visa installments (deductions) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-currency-dollar me-2 text-primary"></i>Salary & Compensation</h5>
                    <a href="{{ route('employees.salary', $employee) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Salary
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $salary = $employee->salaryStructure;
                        $gross = $salary?->gross_salary ?? 0;
                        $food = (float) ($salary->food_deduction ?? 0);
                        $visa = (float) ($salary->visa_deduction ?? 0);
                        $insurance = (float) ($salary->insurance_deduction ?? 0);
                        // In salary_structures the “Other Deduction” column may be stored either as
                        // other_deduction (from older code) OR as other_allowance (from current model/form).
                        $otherDeduction = (float) ($salary->other_deduction ?? $salary->other_allowance ?? 0);
                        $net = $gross - ($food + $visa + $insurance + $otherDeduction);
                    @endphp

                    @if($salary)
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <h6 class="fw-bold mb-3">Earnings</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Basic Salary</span>
                                        <span class="fw-semibold">{{ number_format($salary->basic_salary ?? 0) }} AED</span>
                                    </div>
                                    @if(($salary->housing_allowance ?? 0) > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Housing Allowance</span>
                                        <span>{{ number_format($salary->housing_allowance) }} AED</span>
                                    </div>
                                    @endif
                                    @if(($salary->transport_allowance ?? 0) > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Transport Allowance</span>
                                        <span>{{ number_format($salary->transport_allowance) }} AED</span>
                                    </div>
                                    @endif
                                    @if(($salary->medical_allowance ?? 0) > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Medical Allowance</span>
                                        <span>{{ number_format($salary->medical_allowance) }} AED</span>
                                    </div>
                                    @endif
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Gross Salary</span>
                                        <span class="fw-bold text-primary fs-5">{{ number_format($gross) }} AED</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <h6 class="fw-bold mb-3">Deductions</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Mess/Food</span>
                                        <span class="text-danger">{{ number_format($food) }} AED</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Visa Charges (Fixed)</span>
                                        <span class="text-danger">{{ number_format($visa) }} AED</span>
                                    </div>

                                    @php
                                        $visaInstallments = $employee->advances
                                            ->where('reason', 'Visa Charges (Installments)')
                                            ->where('status', 'active')
                                            ->sortByDesc('id')
                                            ->values();

                                        $visaInstallmentTotal = $visaInstallments->sum('amount');
                                        $visaInstallmentPending = $visaInstallments->sum('pending_amount');
                                    @endphp

                                    @if($visaInstallments->count() > 0)
                                        <div class="mt-3">
                                            

                                            <div class="mt-3">
                                                <small class="text-muted d-block mb-2">Visa Installment Details</small>
                                                <div class="table-responsive">
                                                    <table class="table table-sm align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Installment</th>
                                                                <th>Total</th>
                                                                <th>Pending</th>
                                                                <th>Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($visaInstallments as $vAdv)
                                                                <tr>
                                                                    <td>#{{ $vAdv->id }}</td>
                                                                    <td>{{ number_format((float)($vAdv->installment_amount ?? 0), 2) }} AED</td>
                                                                    <td>{{ $vAdv->paid_installments ?? 0 }}/{{ $vAdv->total_installments ?? 0 }}</td>
                                                                    <td>{{ number_format((float)($vAdv->pending_amount ?? 0), 2) }} AED</td>
                                                                    <td>{{ $vAdv->advance_date?->format('d/m/y') ?? '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Insurance</span>
                                        <span class="text-danger">{{ number_format($insurance) }} AED</span>
                                    </div>
                                    @if($otherDeduction > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Other Deductions</span>
                                        <span class="text-danger">{{ number_format($otherDeduction) }} AED</span>
                                    </div>
                                    @endif
                                    <hr class="my-2">
                                    {{-- <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Net Salary</span>
                                        <span class="fw-bold text-success fs-5">{{ number_format($net) }} AED</span>
                                    </div> --}}
                                </div>
                            </div>

                            @if(($salary->overtime_rate_per_hour ?? 0) > 0 || ($salary->wps_first_transfer_amount ?? 0) > 0)
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="row">
                                        @if(($salary->overtime_rate_per_hour ?? 0) > 0)
                                        <div class="col-md-6">
                                            <i class="bi bi-clock-history me-2 text-primary"></i>
                                            <strong>Overtime Rate:</strong> {{ number_format($salary->overtime_rate_per_hour) }} AED/hour
                                        </div>
                                        @endif
                                        @if(($salary->wps_first_transfer_amount ?? 0) > 0)
                                        <div class="col-md-6">
                                            <i class="bi bi-bank me-2 text-primary"></i>
                                            <strong>WPS First Transfer:</strong> {{ number_format($salary->wps_first_transfer_amount) }} AED
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No salary structure defined. <a href="{{ route('employees.salary', $employee) }}">Set up now</a>.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Advances Summary Card --}}
            @if($employee->advances->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-primary"></i>Advances & Recoveries</h5>
                </div>
                <div class="card-body">
@php
                        // Advances & Recoveries me sirf normal advances dikhane hain.
                        // Visa Charges (Installments) wale advances ko exclude karna hai.
                        $activeAdvances = $employee->advances
                            ->where('status', 'active')
                            ->where('reason', '!=', 'Visa Charges (Installments)');

                        $advTotal = $activeAdvances->sum('amount');
                        $advPending = $activeAdvances->sum('pending_amount');
                        $totalRecovered = $advTotal - $advPending;
                    @endphp
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center">
                                <small class="text-muted">Total Advances</small>
                                <h4 class="mb-0 text-primary">{{ number_format($advTotal) }} AED</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center">
                                <small class="text-muted">Recovered Amount</small>
                                <h4 class="mb-0 text-success">{{ number_format($totalRecovered) }} AED</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 text-center">
                                <small class="text-muted">Pending Amount</small>
                                <h4 class="mb-0 text-warning">{{ number_format($advPending) }} AED</h4>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                    <th>Installments</th>
                                    <th>Pending</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->advances
                                    ->where('reason', '!=', 'Visa Charges (Installments)')
                                    ->sortByDesc('id')
                                    ->take(5) as $adv)
                                <tr>
                                    <td>#{{ $adv->id }}</td>
                                    <td>{{ number_format($adv->amount) }} AED</td>
                                    <td>{{ $adv->reason ?? '-' }}</td>
                                    <td>{{ $adv->advance_date->format('d/m/y') }}</td>
                                    <td>{{ $adv->paid_installments ?? 0 }}/{{ $adv->total_installments ?? 0 }}</td>
                                    <td>{{ number_format($adv->pending_amount ?? 0) }} AED</td>
                                    <td>
                                        @if($adv->status === 'active')
                                            <span class="badge bg-warning">Active</span>
                                        @elseif($adv->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($adv->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('advances.show', $adv) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($employee->advances->count() > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">Showing 5 of {{ $employee->advances->count() }} advances</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Tabs for Additional Information --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">
                                <i class="bi bi-file-earmark-text me-1"></i> Documents
                                <span class="badge bg-secondary ms-1">{{ $employee->documents->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="payrolls-tab" data-bs-toggle="tab" data-bs-target="#payrolls" type="button">
                                <i class="bi bi-receipt me-1"></i> Payroll History
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="custom-tab" data-bs-toggle="tab" data-bs-target="#custom" type="button">
                                <i class="bi bi-grid me-1"></i> Custom Fields
                                <span class="badge bg-secondary ms-1">{{ count($employee->custom_fields ?? []) }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="employeeTabContent">
                        {{-- Documents Tab --}}
                        <div class="tab-pane fade show active" id="documents" role="tabpanel">
                            @if($employee->documents->count())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Type</th>
                                                <th>Document Number</th>
                                                <th>Expiry Date</th>
                                                <th>File</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employee->documents as $doc)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-text me-2 text-primary"></i>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</strong>
                                                </td>
                                                <td>{{ $doc->document_number ?? '-' }}</td>
                                                <td>{{ $doc->expiry_date?->format('d/m/y') ?? '-' }}</td>
                                                <td>
                                                    @if($doc->file_path)
                                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-download"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-muted">No file</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($doc->isExpired())
                                                        <span class="badge bg-danger">Expired</span>
                                                    @elseif($doc->isExpiringSoon())
                                                        <span class="badge bg-warning">Expiring Soon</span>
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
                                <div class="text-center py-5">
                                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No documents uploaded yet</p>
                                </div>
                            @endif
                        </div>

                        {{-- Payroll History Tab --}}
                        <div class="tab-pane fade" id="payrolls" role="tabpanel">
                            @if($employee->payrollRecords->count())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Month</th>
                                                <th>Gross Salary</th>
                                                <th>Total Deductions</th>
                                                <th>Net Salary</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employee->payrollRecords->sortByDesc('payroll_month')->take(10) as $record)
                                            <tr>
                                                <td>{{ method_exists($record->payroll_month, 'format') ? $record->payroll_month->format('M Y') : \Carbon\Carbon::parse($record->payroll_month)->format('M Y') }}</td>
                                                <td>{{ number_format($record->gross_salary) }} AED</td>
                                                <td>{{ number_format($record->total_deductions) }} AED</td>
                                                <td><strong>{{ number_format($record->net_salary) }} AED</strong></td>
                                                <td>
                                                    <span class="badge bg-success">{{ ucfirst($record->status) }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('payroll.slip', $record) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-printer"></i> Slip
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-receipt fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No payroll records found</p>
                                </div>
                            @endif
                        </div>

                        {{-- Custom Fields Tab --}}
                        <div class="tab-pane fade" id="custom" role="tabpanel">
                            @if($employee->custom_fields && count($employee->custom_fields))
                                <div class="row g-3">
                                    @foreach($employee->custom_fields as $key => $value)
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3">
                                                <small class="text-muted d-block mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</small>
                                                <span class="fw-semibold">{{ $value }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-grid fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No custom fields defined</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2B5797 0%, #1E3A6F 100%);
    }
    
    .card {
        border-radius: 1rem;
        overflow: hidden;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.08);
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        color: #2B5797;
        border-bottom: 2px solid #2B5797;
        background: transparent;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #2B5797;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }
    
    .btn {
        font-weight: 500;
    }
    
    .hover-shadow:hover {
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endsection
