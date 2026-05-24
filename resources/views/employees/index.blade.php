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
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Name, Email, Phone...">
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
        </div>
    </div>

    {{-- Employees Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="employeesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">Photo</th>
                            <th style="width: 20%">Employee Details</th>
                            <th style="width: 15%">Department</th>
                            <th style="width: 15%">Designation</th>
                            <th style="width: 12%">Basic Salary</th>
                            <th style="width: 8%">Status</th>
                            <th style="width: 10%">Join Date</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="tableInfo" class="text-muted small"></span>
                </div>
                <div id="paginationLinks">
                    <!-- Pagination will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function() {
    let dataTable = null;
    
    // Initialize DataTable
    function initDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }
        
        dataTable = $('#employeesTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('employees.ajax') }}",
                type: "GET",
                data: function(d) {
                    d.search = $('#searchInput').val();
                    d.department = $('#departmentFilter').val();
                    d.status = $('#statusFilter').val();
                    d.per_page = $('#perPageFilter').val();
                },
                dataSrc: function(json) {
                    // Update info and pagination
                    $('#tableInfo').html(`Showing ${json.from || 0} to ${json.to || 0} of ${json.total || 0} entries`);
                    $('#paginationLinks').html(json.pagination);
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'photo',
                    render: function(data, type, row) {
                        if (data) {
                            return `<img src="/storage/${data}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">`;
                        } else {
                            const initials = (row.first_name?.charAt(0) || '') + (row.last_name?.charAt(0) || '');
                            return `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:0.8rem;font-weight:600;">${initials || 'N/A'}</div>`;
                        }
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <div class="fw-semibold">${row.full_name}</div>
                            <small class="text-muted">${row.employee_code}</small>
                            ${row.email ? `<br><small class="text-muted"><i class="bi bi-envelope"></i> ${row.email}</small>` : ''}
                        `;
                    }
                },
                {
                    data: 'department_name',
                    defaultContent: '-'
                },
                {
                    data: 'designation_name',
                    defaultContent: '-'
                },
                {
                    data: 'basic_salary',
                    render: function(data) {
                        if (!data) return '-';
                        return new Intl.NumberFormat('en-AE', { style: 'currency', currency: 'AED' }).format(data);
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        if (data === 'active') {
                            return '<span class="badge bg-success">Active</span>';
                        } else {
                            return '<span class="badge bg-secondary">Inactive</span>';
                        }
                    }
                },
                {
                    data: 'joining_date',
                    render: function(data) {
                        if (!data) return '-';
                        return new Date(data).toLocaleDateString('en-GB');
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <a href="/employees/${row.id}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/employees/${row.id}/edit" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteEmployee(${row.id}, '${row.full_name}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                emptyTable: '<div class="text-center py-4"><i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>No employees found. <a href="{{ route("employees.create") }}">Add first one</a>?</div>',
                zeroRecords: '<div class="text-center py-4"><i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>No matching employees found</div>'
            },
            order: [[1, 'asc']],
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            dom: 'rt' // Disable default DataTable search/pagination, we'll use custom
        });
    }
    
    // Initialize DataTable
    initDataTable();
    
    // Reload table when filters change
    function reloadTable() {
        if (dataTable) {
            dataTable.ajax.reload();
        }
    }
    
    // Debounce function for search input
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
        $('#perPageFilter').val('25');
        reloadTable();
    });
});

// Delete employee function
function deleteEmployee(id, name) {
    if (confirm(`Are you sure you want to delete employee "${name}"?`)) {
        $.ajax({
            url: `/employees/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Reload the table
                    $('#employeesTable').DataTable().ajax.reload();
                    // Show success message
                    showToast('success', response.message || 'Employee deleted successfully');
                } else {
                    showToast('error', response.message || 'Error deleting employee');
                }
            },
            error: function(xhr) {
                showToast('error', 'An error occurred while deleting the employee');
            }
        });
    }
}

function showToast(type, message) {
    // You can implement toast notifications here
    // For now, use alert
    alert(message);
}
</script>

<style>
    .dataTables_processing {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        background: rgba(255,255,255,0.9) !important;
        padding: 20px !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
        z-index: 9999 !important;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
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
@endsection