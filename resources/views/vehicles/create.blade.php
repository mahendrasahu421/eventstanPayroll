@extends('layouts.app')

@section('title', $vehicle->exists ? 'Edit Vehicle' : 'Add Vehicle')

@section('content')
<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-truck-front text-primary me-2"></i>{{ $vehicle->exists ? 'Edit Vehicle' : 'Add Vehicle' }}</h2>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $vehicle->exists ? route('vehicles.update', $vehicle) : route('vehicles.store') }}">
            @csrf
            @if($vehicle->exists)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vehicle Code</label>
                    <input type="text" name="vehicle_code" class="form-control @error('vehicle_code') is-invalid @enderror" value="{{ old('vehicle_code', $vehicle->vehicle_code) }}">
                    @error('vehicle_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Plate Number <span class="text-danger">*</span></label>
                    <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror" value="{{ old('plate_number', $vehicle->plate_number) }}" required>
                    @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                    <input type="text" name="vehicle_name" class="form-control @error('vehicle_name') is-invalid @enderror" value="{{ old('vehicle_name', $vehicle->vehicle_name) }}" required>
                    @error('vehicle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vehicle Type</label>
                    <input type="text" name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}" placeholder="Bus, Van, Truck">
                    @error('vehicle_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Registration Expiry</label>
                    <input type="date" name="registration_expiry_date" class="form-control" value="{{ old('registration_expiry_date', $vehicle->registration_expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Insurance Expiry</label>
                    <input type="date" name="insurance_expiry_date" class="form-control" value="{{ old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Permit Expiry</label>
                    <input type="date" name="permit_expiry_date" class="form-control" value="{{ old('permit_expiry_date', $vehicle->permit_expiry_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isActive">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Save Vehicle</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
