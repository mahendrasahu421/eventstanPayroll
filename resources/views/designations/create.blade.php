{{-- resources/views/designations/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Designation')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-plus-circle text-success me-2"></i>Add Designation
        </h2>
        <div class="text-muted">Create a new designation</div>
    </div>
    <div>
        <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-briefcase me-2"></i>Designation Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('designations.store') }}">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="department_id" class="form-label fw-semibold">
                                Department <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="name" class="form-label fw-semibold">
                                Designation Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="Enter designation name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">e.g., Software Engineer, Senior Manager, Team Lead</small>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Designation
                        </button>
                        <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-1"></i>Information Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Tips:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Select the department first</li>
                        <li>Designation name should be unique</li>
                        <li>Inactive designations won't appear in dropdowns</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection