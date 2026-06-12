{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                <i class="bi bi-people fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Employees</h1>
                <p class="text-muted mb-0">Manage your workforce efficiently</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.import.form') }}" class="btn btn-outline-success">
                <i class="bi bi-upload me-1"></i> Import
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add Employee
            </a>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Department</label>
                    <select id="departmentFilter" class="form-select">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search employee...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Per Page</label>
                    <select id="perPageFilter" class="form-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Employees Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="employeesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Employee Details</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Basic Salary</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        margin-left: 0.5rem;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        margin: 0 0.5rem;
    }
    .rounded-circle {
        object-fit: cover;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .badge {
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#employeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('employees.ajax') }}",
            type: "GET",
            data: function(d) {
                d.department = $('#departmentFilter').val();
                d.status = $('#statusFilter').val();
                d.per_page = $('#perPageFilter').val();
                d.search = $('#searchInput').val();
            },
            error: function(xhr, error, thrown) {
                console.log('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error loading data'
                });
            }
        },
        columns: [
            { data: 'photo', name: 'photo', orderable: false, searchable: false },
            { data: 'employee_details', name: 'employee_details', orderable: false, searchable: false },
            { data: 'department_name', name: 'department.name' },
            { data: 'designation_name', name: 'designation.name' },
            { data: 'basic_salary', name: 'basic_salary' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'joining_date', name: 'joining_date' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No employees found",
            zeroRecords: "No matching employees found",
            info: "Showing _START_ to _END_ of _TOTAL_ employees",
            infoEmpty: "Showing 0 to 0 of 0 employees",
            infoFiltered: "(filtered from _MAX_ total employees)",
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    // Reload table when filters change
    function reloadTable() {
        table.ajax.reload();
    }

    // Debounce search input
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            reloadTable();
        }, 500);
    });

    $('#departmentFilter, #statusFilter, #perPageFilter').on('change', function() {
        reloadTable();
    });

    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#departmentFilter').val('');
        $('#statusFilter').val('');
        $('#perPageFilter').val('10');
        reloadTable();
    });

    // Delete function
    window.deleteEmployee = function(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete employee "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/employees/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Could not delete employee';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message
                        });
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection