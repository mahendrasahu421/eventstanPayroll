@extends('layouts.app')

@section('title', 'New Advance Payment')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-plus-circle fs-2 text-success"></i>
    <h2>Record Advance Payment</h2>
</div>

<form method="POST" action="{{ route('advances.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Employee *</label>
            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                @endforeach
            </select>
            @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Advance Date *</label>
            <input type="date" name="advance_date" class="form-control @error('advance_date') is-invalid @enderror" value="{{ old('advance_date') }}" required>
            @error('advance_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Amount *</label>
            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" step="0.01" min="1" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Installment Amount *</label>
            <input type="number" name="installment_amount" class="form-control @error('installment_amount') is-invalid @enderror" value="{{ old('installment_amount') }}" step="0.01" min="1" required>
            @error('installment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Total Installments *</label>
            <input type="number" name="total_installments" class="form-control @error('total_installments') is-invalid @enderror" value="{{ old('total_installments') }}" min="1" required>
            @error('total_installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-8">
            <label class="form-label">Reason (Optional)</label>
            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="2">{{ old('reason') }}</textarea>
            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


    </div>

    <div class="card mt-4">
        <div class="card-header"><strong>Summary</strong></div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4"><strong>Total Amount:</strong> <span id="totalAmount">0</span></div>
                <div class="col-md-4"><strong>Monthly Payment:</strong> <span id="monthlyPayment">0</span></div>
                <div class="col-md-4"><strong>Duration:</strong> <span id="duration">0 months</span></div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('advances.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Record Advance</button>
    </div>
</form>

<script>
document.querySelectorAll('input[name="amount"], input[name="installment_amount"], input[name="total_installments"]').forEach(input => {
    input.addEventListener('input', calculateSummary);
});

function calculateSummary() {
    const amount = parseFloat(document.querySelector('input[name="amount"]').value) || 0;
    const installment = parseFloat(document.querySelector('input[name="installment_amount"]').value) || 0;
    const totalInstallments = parseInt(document.querySelector('input[name="total_installments"]').value) || 0;

    document.getElementById('totalAmount').textContent = new Intl.NumberFormat().format(amount);
    document.getElementById('monthlyPayment').textContent = new Intl.NumberFormat().format(installment);
    document.getElementById('duration').textContent = totalInstallments + ' months';
}
</script>
@endsection
