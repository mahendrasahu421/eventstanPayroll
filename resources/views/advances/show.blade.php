@extends('layouts.app')

@section('title', 'Advance #' . $advance->id)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Advance Payment #{{ $advance->id }}</h4>
                <span class="badge fs-6 {{ $advance->status == 'completed' ? 'bg-success' : 'bg-warning' }}">
                    {{ ucfirst($advance->status) }}
                </span>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Employee:</strong> {{ $advance->employee?->full_name ?? '-' }}<br>
                        <strong>Code:</strong> {{ $advance->employee?->employee_code ?? '-' }}<br>
                        <strong>Department:</strong> {{ $advance->employee?->department?->name ?? '-' }}
                    </div>

                    <div class="col-md-6 text-md-end">
                        <h3 class="text-primary mb-1">{{ number_format($advance->amount) }}</h3>
                        <strong class="me-2">Pending:</strong>
                        <span class="badge {{ $advance->pending_amount > 0 ? 'bg-warning' : 'bg-success' }} fs-6">
                            {{ number_format($advance->pending_amount) }}
                        </span>
                    </div>
                </div>

                <!-- Recovery Schedule -->
                @if($advance->recoveries->count())
                    <h6 class="mb-3"><i class="bi bi-calendar3 me-2"></i>Recovery Schedule</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Payroll Month</th>
                                    <th>Amount Recovered</th>
                                    <th>Payroll Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($advance->recoveries as $recovery)
                                    <tr>
                                        <td>{{ $recovery->payrollRecord?->payroll_month ?? '-' }}</td>
                                        <td>{{ number_format($recovery->amount) }}</td>
                                        <td><span class="badge bg-info">{{ $recovery->payrollRecord?->status ?? 'N/A' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <a href="{{ route('advances.index') }}" class="btn btn-outline-secondary w-100 mb-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        @if($advance->status != 'cancelled')
            <form method="POST" action="{{ route('advances.destroy', $advance) }}" class="mb-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Cancel this advance?');">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i>Details</h6>
        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Date:</strong> {{ optional($advance->advance_date)->format('d/m/y') ?? '-' }}</li>
                <li class="list-group-item"><strong>Installment:</strong> {{ number_format($advance->installment_amount) }} x {{ $advance->total_installments }}</li>

                @if($advance->reason)
                    <li class="list-group-item"><strong>Reason:</strong> {{ $advance->reason }}</li>
                @endif
                <li class="list-group-item">
                    <strong>Receipt:</strong>
                    @if($advance->receipt_path)
                        <a href="{{ route('advances.receipt', $advance) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-receipt"></i> View Receipt
                        </a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </li>

                <li class="list-group-item">
                    <strong>Created:</strong>
                    {{ optional($advance->created_at)->format('d/m/y H:i') ?? '-' }}
                    @if(isset($advance->createdBy?->name)) by {{ $advance->createdBy?->name }} @endif
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

