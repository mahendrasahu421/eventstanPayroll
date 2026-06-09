<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::query()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $expiringVehicles = Vehicle::query()
            ->where('is_active', true)
            ->where(function ($query) {
                foreach (['registration_expiry_date', 'insurance_expiry_date', 'permit_expiry_date'] as $field) {
                    $query->orWhereBetween($field, [now()->toDateString(), now()->addMonth()->toDateString()]);
                }
            })
            ->orderBy('registration_expiry_date')
            ->get();

        return view('vehicles.index', compact('vehicles', 'expiringVehicles'));
    }

    public function create()
    {
        return view('vehicles.create', ['vehicle' => new Vehicle()]);
    }

    public function store(Request $request)
    {
        Vehicle::create($this->validatedData($request));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.create', compact('vehicle'));
    }

    public function show(Vehicle $vehicle)
    {
        return redirect()->route('vehicles.edit', $vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $vehicle->update($this->validatedData($request, $vehicle));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    private function validatedData(Request $request, ?Vehicle $vehicle = null): array
    {
        $vehicleId = $vehicle?->id;

        $data = $request->validate([
            'vehicle_code' => "nullable|string|max:50|unique:vehicles,vehicle_code,{$vehicleId}",
            'plate_number' => "required|string|max:50|unique:vehicles,plate_number,{$vehicleId}",
            'vehicle_name' => 'required|string|max:150',
            'vehicle_type' => 'nullable|string|max:100',
            'registration_expiry_date' => 'nullable|date',
            'insurance_expiry_date' => 'nullable|date',
            'permit_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        return $data;
    }
}
