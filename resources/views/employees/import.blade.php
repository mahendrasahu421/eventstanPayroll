@extends('layouts.app')

@section('title', 'Import Employees')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-upload fs-2 text-success"></i>
    <h2>Bulk Import Employees</h2>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Upload Excel/CSV file</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Download <a href="{{ route('employees.import.template') }}" class="alert-link">sample template</a> to get the
            correct format.
        </div>

        <form method="POST" action="{{ route('employees.import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="file" class="form-label">Select Excel/CSV file</label>
                <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror"
accept=".xlsx,.csv"
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i>Import
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <strong>Sample Template Columns</strong>
    </div>
    <div class="card-body">
        <ul class="row g-2 text-sm">
            @foreach ([
                'company' => 'Company name',
                'company_code' => 'Company code',
                'first_name' => 'First name',
                'last_name' => 'Last name',
                'email' => 'Email (optional)',
                'phone' => 'Phone (optional)',
                'date_of_birth' => 'Date of Birth',
                'department' => 'Department name',
                'designation' => 'Designation name',
                'nationality' => 'Nationality',
                'wps_personal_number' => 'WPS number (optional)',
                'joining_date' => 'Joining date',
                'status' => 'active or inactive',
                'bank_name' => 'Bank name (optional)',
                'bank_account_number' => 'Bank account number (optional)',
                'iban' => 'IBAN (optional)',
                'address' => 'Address (optional)',

                'basic_salary' => 'Basic salary',
                'increment_value' => 'Increment value',
                'overtime_rate_per_hour' => 'Overtime rate',
                'wps_first_transfer_amount' => 'WPS salary / 1st transfer',
                'food_deduction' => 'Food deduction',
                'visa_deduction' => 'Visa deduction',
                'total_installments' => 'Visa total installments',
                'insurance_deduction' => 'Insurance deduction',

                'advance_payment' => 'Advance Payment',
                'advance_date' => 'Advance Date',

                'Passport Number' => 'Passport Number',
                'Passport expiry date' => 'Passport expiry date',
                'Emirates ID Number' => 'Emirates ID Number',
                'Emirated ID expiry date' => 'Emirated ID expiry date',

                'Insurance policy number' => 'Insurance policy number',
                'Insurance card number' => 'Insurance card number',
                'Insurance start date' => 'Insurance start date',
                'Insurance End date' => 'Insurance End date',

                'Other Deductions' => 'Other Deductions',
            ] as $column => $label)


                <li class="col-md-6">
                    <span class="badge bg-light text-dark me-2">{{ $column }}</span>{{ $label }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
