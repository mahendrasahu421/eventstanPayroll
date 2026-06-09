{{-- resources/views/departments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-diagram-3 text-primary me-2"></i>Departments
        </h2>
        <div class="text-muted">Manage all departments</div>
    </div>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#departmentModal" onclick="resetForm()">
            <i class="bi bi-plus-circle"></i> Add Department
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="departmentsTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
              
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Create/Edit -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalTitle">
                    <i class="bi bi-plus-circle me-2"></i>Add Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="departmentForm">
                    @csrf
                    <input type="hidden" id="department_id" name="department_id">
                    <input type="hidden" id="_method" name="_method">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label fw-semibold">
                                Department Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Enter department name">
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>

                        <div class="col-md-12">
                            <label for="code" class="form-label fw-semibold">
                                Department Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   placeholder="Enter department code (e.g., HR, IT, FIN)">
                            <div class="invalid-feedback" id="code_error"></div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Enter department description"></textarea>
                            <div class="invalid-feedback" id="description_error"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn">
                    <i class="bi bi-save"></i> Save Department
                </button>
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
    .status-badge {
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .status-badge:hover {
        opacity: 0.8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#departmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('departments.data') }}",
            type: "GET",
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
    { data: 'id', name: 'id' },
    { data: 'name', name: 'name' },
    { data: 'description', name: 'description' },
    { data: 'status', name: 'is_active', orderable: false },
    {
        data: 'created_at',
        name: 'created_at',
        render: function(data) {
            if (!data) return '';
            
            let date = new Date(data);
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = String(date.getFullYear()).slice(-2);

            return `${day}-${month}-${year}`;
        }
    },
    { data: 'actions', name: 'actions', orderable: false, searchable: false }
],
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No departments found",
            zeroRecords: "No matching departments found",
            info: "Showing _START_ to _END_ of _TOTAL_ departments",
            infoEmpty: "Showing 0 to 0 of 0 departments",
            infoFiltered: "(filtered from _MAX_ total departments)",
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

    // Save button click
    $('#saveBtn').on('click', function() {
        var id = $('#department_id').val();
        var url = id ? '/master/departments/' + id : '/master/departments';
        var method = id ? 'PUT' : 'POST';
        
        // Clear previous errors
        clearErrors();
        
        var formData = $('#departmentForm').serialize();
        if (id) {
            formData += '&_method=' + method;
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#departmentModal').modal('hide');
                    resetForm();
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong!'
                    });
                }
            }
        });
    });

    // Edit button click (using event delegation)
    $('#departmentsTable').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '/master/departments/' + id + '/edit',
            type: 'GET',
            success: function(response) {
                $('#department_id').val(response.id);
                $('#name').val(response.name);
                $('#code').val(response.code);
                $('#description').val(response.description || '');
                
                $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Department');
                $('#saveBtn').html('<i class="bi bi-save"></i> Update Department');
                $('#departmentModal').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Could not load department data'
                });
            }
        });
    });

    // Delete button click
    $('#departmentsTable').on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete department "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/master/departments/' + id,
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
                        var message = xhr.responseJSON?.message || 'Could not delete department';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message
                        });
                    }
                });
            }
        });
    });

    // Reset form function
    window.resetForm = function() {
        $('#departmentForm')[0].reset();
        $('#department_id').val('');
        $('#modalTitle').html('<i class="bi bi-plus-circle me-2"></i>Add Department');
        $('#saveBtn').html('<i class="bi bi-save"></i> Save Department');
        clearErrors();
    };

    // Clear validation errors
    function clearErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').html('');
    }

    // Display validation errors
    function displayErrors(errors) {
        $.each(errors, function(field, messages) {
            var input = $('#' + field);
            input.addClass('is-invalid');
            $('#' + field + '_error').html(messages[0]);
        });
    }
});
</script>
@endpush
@endsection