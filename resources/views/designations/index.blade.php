{{-- resources/views/designations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Designations')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-building text-primary me-2"></i>Designations
        </h2>
        <div class="text-muted">Manage designation roles</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('designations.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add Designation
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filters --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Designation name...">
            </div>
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

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="designationsTable">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#designationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('designations.data') }}",
            type: "GET",
            data: function(d) {
                d.search = $('#searchInput').val();
                d.department = $('#departmentFilter').val();
                d.status = $('#statusFilter').val();
                d.per_page = $('#perPageFilter').val();
            }
        },
        columns: [
            { data: 'department_name', name: 'department_name', defaultContent: '-' },
            { data: 'name', name: 'name', defaultContent: '-' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No designations found",
            zeroRecords: "No matching designations found",
            info: "Showing _START_ to _END_ of _TOTAL_ designations",
            infoEmpty: "Showing 0 to 0 of 0 designations",
            infoFiltered: "(filtered from _MAX_ total designations)",
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            paginate: { first: "First", last: "Last", next: "Next", previous: "Previous" }
        }
    });

    function reloadTable() {
        table.ajax.reload();
    }

    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => reloadTable(), 500);
    });

    $('#departmentFilter, #statusFilter, #perPageFilter').on('change', function() {
        reloadTable();
    });

    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#departmentFilter').val('');
        $('#statusFilter').val('');
        $('#perPageFilter').val('25');
        reloadTable();
    });

    // Called from controller-generated HTML in `actions` column
    window.deleteDesignation = function(id, name) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete designation "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('master/designations') }}/${id}`,
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
                            Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                        }
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON?.message || 'Could not delete designation';
                        Swal.fire({ icon: 'error', title: 'Error!', text: message });
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection

