@extends('layouts.app')

@section('title', 'Vehicle Master')

@section('content')
<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-truck-front text-primary me-2"></i>Vehicle Master</h2>
        <div class="text-muted">Track vehicles and expiry alerts</div>
    </div>
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Vehicle
    </a>
</div>

@if($expiringVehicles->count())
    <div class="alert alert-warning">
        <div class="fw-semibold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Expiring within one month</div>
        <div class="d-flex flex-column gap-1">
            @foreach($expiringVehicles as $vehicle)
                <div>{{ $vehicle->plate_number }} - {{ $vehicle->vehicle_name }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Plate</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Registration</th>
                        <th>Insurance</th>
                        <th>Permit</th>
                        <th>Status</th>
                        <th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->vehicle_code ?? '-' }}</td>
                            <td class="fw-semibold">{{ $vehicle->plate_number }}</td>
                            <td>{{ $vehicle->vehicle_name }}</td>
                            <td>{{ $vehicle->vehicle_type ?? '-' }}</td>
                            @foreach(['registration_expiry_date', 'insurance_expiry_date', 'permit_expiry_date'] as $field)
                                @php($status = $vehicle->expiryStatus($field))
                                <td>
                                    <div>{{ $vehicle->{$field}?->format('d/m/y') ?? '-' }}</div>
                                    @if($status === 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($status === 'expiring')
                                        <span class="badge bg-warning text-dark">Expiring Soon</span>
                                    @elseif($status === 'valid')
                                        <span class="badge bg-success">Valid</span>
                                    @else
                                        <span class="badge bg-secondary">Missing</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <span class="badge {{ $vehicle->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $vehicle->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Delete this vehicle?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">No vehicles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 d-flex justify-content-center">{{ $vehicles->links() }}</div>
    </div>
</div>
@endsection
