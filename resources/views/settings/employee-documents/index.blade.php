@extends('layouts.app')

@section('title', 'Employee Documents')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-file-earmark-text me-2"></i>Employee Documents</h2>
        <div class="text-muted">Filter by department or employee, then click an employee to see all their documents.</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Filters</strong>
    </div>
    <div class="card-body">
        <form id="documentFilters" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Document, employee, code">
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label">Document Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    @foreach($docTypes as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-4">
                <label class="form-label">Expired</label>
                <select name="expired" class="form-select">
                    <option value="">Any</option>
                    <option value="yes" @selected(request('expired') === 'yes')>Yes</option>
                    <option value="no" @selected(request('expired') === 'no')>No</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-4">
                <label class="form-label">Expiring within 30 days</label>
                <select name="expiring" class="form-select">
                    <option value="">Any</option>
                    <option value="yes" @selected(request('expiring') === 'yes')>Yes</option>
                    <option value="no" @selected(request('expiring') === 'no')>No</option>
                </select>
            </div>

            <div class="col-lg-6 col-md-4 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
                <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>All Employee Documents</strong>
            <span class="text-muted ms-2">(AJAX DataTable)</span>
        </div>
        <div class="text-muted small">
            Click an employee name to open all documents for that employee.
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="employeeDocumentsTable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Issue Date</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    const table = $('#employeeDocumentsTable').DataTable({
        processing: true,
        searching: false,
        lengthChange: true,
        pageLength: 25,
        order: [[5, 'asc']],
        ajax: {
            url: @json(route('employee-documents')),
            data: function (d) {
                const form = document.getElementById('documentFilters');
                const formData = new FormData(form);
                formData.forEach((value, key) => {
                    d[key] = value;
                });
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('employee_id', row.employee_id);
                    const employeeLink = url.pathname + '?' + url.searchParams.toString();

                    return `
                        <div class="fw-semibold">
                            <a href="${employeeLink}" class="text-decoration-none">${$('<div>').text(row.employee_name).html()}</a>
                        </div>
                        <div class="text-muted small">${$('<div>').text(row.employee_code).html()}</div>
                    `;
                }
            },
            {
                data: 'department_name',
                render: function (data) {
                    return $('<div>').text(data || '-').html();
                }
            },
            {
                data: 'document_type',
                render: function (data) {
                    return $('<div>').text(data ? data.replaceAll('_', ' ') : '-').html();
                }
            },
            {
                data: 'document_number',
                render: function (data) {
                    return $('<div>').text(data || '-').html();
                }
            },
            {
                data: 'issue_date',
                render: function (data) {
                    return $('<div>').text(data || '-').html();
                }
            },
            {
                data: 'expiry_date',
                render: function (data) {
                    return $('<div>').text(data || '-').html();
                }
            },
            {
                data: 'status',
                render: function (data) {
                    let badgeClass = 'badge bg-secondary';
                    if (data === 'Valid') badgeClass = 'badge badge-active';
                    if (data === 'Expired') badgeClass = 'badge badge-inactive';
                    if (data === 'Expiring') badgeClass = 'badge' + ' ' + 'bg-warning text-dark';
                    return `<span class="${badgeClass}">${$('<div>').text(data || 'N/A').html()}</span>`;
                }
            },
            {
                data: 'file_url',
                orderable: false,
                render: function (data) {
                    if (!data) {
                        return '<span class="text-muted">-</span>';
                    }
                    return `<a class="btn btn-sm btn-outline-primary" target="_blank" href="${data}"><i class="bi bi-box-arrow-up-right"></i> View</a>`;
                }
            }
        ]
    });

    $('#documentFilters').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $('#resetFilters').on('click', function () {
        $('#documentFilters')[0].reset();
        table.ajax.reload();
    });

    $('#documentFilters select, #documentFilters input').on('change keyup', function () {
        clearTimeout(window.__employeeDocumentFilterTimer);
        window.__employeeDocumentFilterTimer = setTimeout(function () {
            table.ajax.reload();
        }, 250);
    });
});
</script>
@endpush
@endsection
