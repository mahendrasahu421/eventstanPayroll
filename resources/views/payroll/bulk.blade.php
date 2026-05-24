@extends('layouts.app')

@section('title', 'Bulk Payroll Processing')

@section('content')
<div class="container-fluid px-4">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                <i class="bi bi-lightning-charge fs-2 text-warning"></i>
            </div>
            <div>
                <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">Bulk Payroll Processing</h1>
                <p class="text-muted mb-0">Process payroll for multiple employees at once via Excel import</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.process') }}" class="btn btn-outline-primary">
                <i class="bi bi-person me-1"></i> Single Employee
            </a>
        </div>
    </div>

    {{-- FIRST ROW: Upload & Download Template Side by Side --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        {{-- Upload Section --}}
                        <div class="col-md-6">
                            <form id="excelImportForm" enctype="multipart/form-data">
                                @csrf
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="flex-grow-1">
                                        <label class="form-label fw-semibold mb-1">Upload Excel File</label>
                                        <input type="file" name="excel_file" id="excelFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                                        <small class="text-muted">Supported formats: .xlsx, .xls, .csv</small>
                                    </div>
                                    <button type="submit" class="btn btn-success mt-3 mt-md-0" id="importBtn">
                                        <i class="bi bi-upload me-1"></i> Import & Preview
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        {{-- Download Template Section --}}
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="bg-light rounded-3 p-3 d-inline-block">
                                <i class="bi bi-download me-2 text-success"></i>
                                <strong>Need a template?</strong>
                                <a href="{{ route('payroll.download-template') }}" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Download Excel Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECOND ROW: Full Width Table & Processing Options --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-table me-2 text-primary"></i>
                        Payroll Data Preview
                        <span id="recordCount" class="badge bg-secondary ms-2">0</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning" id="editModeBtn">
                            <i class="bi bi-pencil me-1"></i> Edit Mode
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="payrollTable" style="min-width: 1000px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Employee Code</th>
                                    <th>Employee Name</th>
                                    <th>Basic Salary</th>
                                    <th>Present Days</th>
                                    <th>OT Hours</th>
                                    <th>Food Ded.</th>
                                    <th>Other Ded.</th>
                                    <th>Net Salary</th>
                                </tr>
                            </thead>
                            <tbody id="payrollTableBody">
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-cloud-upload fs-1 mb-3 d-block"></i>
                                        Upload Excel file to preview data
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent" id="tableFooter" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-4 flex-wrap">
                            <div>
                                <span class="text-muted">Selected:</span>
                                <strong id="selectedCount">0</strong> <span class="text-muted">employees</span>
                            </div>
                            <div>
                                <span class="text-muted">Total Net Salary:</span>
                                <strong id="totalNetSalary" class="text-primary ms-1">AED 0</strong>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <div>
                                <label class="form-label mb-0 me-2">Payroll Month:</label>
                                <input type="month" name="payroll_month" id="payrollMonth" class="form-control form-control-sm d-inline-block" style="width: auto;" value="{{ now()->format('Y-m') }}" required>
                            </div>
                            <div>
                                <label class="form-label mb-0 me-2">Working Days:</label>
                                <input type="number" name="working_days" id="workingDays" class="form-control form-control-sm d-inline-block" style="width: 80px;" value="30" min="1" max="31">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="processAllBtn" disabled>
                                <i class="bi bi-check2-all me-1"></i> Process Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
$(document).ready(function() {
    let payrollData = [];
    let editMode = false;
    
    // Excel Import Handler
    $('#excelImportForm').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('excelFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('Please select an Excel file', 'danger');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet);
            processExcelData(jsonData);
        };
        reader.readAsArrayBuffer(file);
    });
    
    function processExcelData(data) {
        if (!data || data.length === 0) {
            showAlert('No data found in Excel file', 'warning');
            return;
        }
        
        payrollData = data.map(row => ({
            employee_code: row['Employee Code'] || row['employee_code'] || '',
            employee_name: row['Employee Name'] || row['employee_name'] || '',
            employee_id: row['Employee ID'] || row['employee_id'] || null,
            basic_salary: parseFloat(row['Basic Salary'] || row['basic_salary'] || 0),
            present_days: parseInt(row['Present Days'] || row['present_days'] || 30),
            overtime_hours: parseFloat(row['Overtime Hours'] || row['overtime_hours'] || 0),
            food_deduction: parseFloat(row['Food Deduction'] || row['food_deduction'] || 0),
            other_deduction: parseFloat(row['Other Deduction'] || row['other_deduction'] || 0),
            visa_deduction: parseFloat(row['Visa Deduction'] || row['visa_deduction'] || 0),
            insurance_deduction: parseFloat(row['Insurance Deduction'] || row['insurance_deduction'] || 0),
            advance_deduction: parseFloat(row['Advance Deduction'] || row['advance_deduction'] || 0),
            selected: true
        }));
        
        renderTable();
        $('#processAllBtn').prop('disabled', false);
        showAlert(`Loaded ${payrollData.length} employees from Excel`, 'success');
    }
    
    function calculateNetSalary(item) {
        const dailyRate = item.basic_salary / 30;
        const daysWorkedAmount = dailyRate * item.present_days;
        const overtimeAmount = (item.overtime_hours || 0) * (item.basic_salary / 30 / 8);
        const grossSalary = daysWorkedAmount + overtimeAmount;
        const totalDeductions = (item.food_deduction || 0) + (item.other_deduction || 0) + 
                               (item.visa_deduction || 0) + (item.insurance_deduction || 0) + 
                               (item.advance_deduction || 0);
        return grossSalary - totalDeductions;
    }
    
    function renderTable() {
        const tbody = $('#payrollTableBody');
        tbody.empty();
        
        if (payrollData.length === 0) {
            tbody.html('<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 mb-3 d-block"></i>No data to display</td></tr>');
            $('#tableFooter').hide();
            return;
        }
        
        payrollData.forEach((item, index) => {
            const netSalary = calculateNetSalary(item);
            
            const row = `
                <tr data-index="${index}" class="${item.selected ? '' : 'table-secondary'}">
                    <td><input type="checkbox" class="employee-checkbox" data-index="${index}" ${item.selected ? 'checked' : ''}></td>
                    <td class="fw-medium">${escapeHtml(item.employee_code)}</td>
                    <td>${editMode ? `<input type="text" class="form-control form-control-sm edit-input" data-field="employee_name" value="${escapeHtml(item.employee_name)}">` : `<span class="fw-semibold">${escapeHtml(item.employee_name)}</span>`}</td>
                    <td>${editMode ? `<input type="number" class="form-control form-control-sm edit-input" data-field="basic_salary" value="${item.basic_salary}">` : formatNumber(item.basic_salary)}</td>
                    <td>${editMode ? `<input type="number" class="form-control form-control-sm edit-input" data-field="present_days" value="${item.present_days}" min="0" max="31">` : item.present_days}</td>
                    <td>${editMode ? `<input type="number" class="form-control form-control-sm edit-input" data-field="overtime_hours" value="${item.overtime_hours}" step="0.5">` : item.overtime_hours}</td>
                    <td>${editMode ? `<input type="number" class="form-control form-control-sm edit-input" data-field="food_deduction" value="${item.food_deduction}">` : formatNumber(item.food_deduction)}</td>
                    <td>${editMode ? `<input type="number" class="form-control form-control-sm edit-input" data-field="other_deduction" value="${item.other_deduction}">` : formatNumber(item.other_deduction)}</td>
                    <td class="fw-bold text-primary">${formatNumber(netSalary)}</td>
                </tr>
            `;
            tbody.append(row);
        });
        
        if (editMode) {
            $('.edit-input').on('change', function() {
                const row = $(this).closest('tr');
                const index = row.data('index');
                const field = $(this).data('field');
                let value = $(this).val();
                
                if (payrollData[index]) {
                    if (field === 'employee_name') {
                        payrollData[index][field] = value;
                    } else {
                        payrollData[index][field] = parseFloat(value) || 0;
                    }
                    updateRowCalculation(index);
                }
            });
        }
        
        updateSummary();
        $('#tableFooter').show();
        $('#recordCount').text(payrollData.length);
    }
    
    function updateRowCalculation(index) {
        const netSalary = calculateNetSalary(payrollData[index]);
        $(`tr[data-index="${index}"] td:last-child`).text(formatNumber(netSalary));
        updateSummary();
    }
    
    function updateSummary() {
        const selectedItems = payrollData.filter(item => item.selected);
        $('#selectedCount').text(selectedItems.length);
        
        let totalNet = 0;
        selectedItems.forEach(item => {
            totalNet += calculateNetSalary(item);
        });
        $('#totalNetSalary').text(formatNumber(totalNet));
    }
    
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        payrollData.forEach(item => item.selected = isChecked);
        $('.employee-checkbox').prop('checked', isChecked);
        updateSummary();
    });
    
    $(document).on('change', '.employee-checkbox', function() {
        const index = $(this).data('index');
        payrollData[index].selected = $(this).is(':checked');
        updateSummary();
    });
    
    $('#editModeBtn').on('click', function() {
        editMode = !editMode;
        $(this).html(editMode ? '<i class="bi bi-eye me-1"></i> View Mode' : '<i class="bi bi-pencil me-1"></i> Edit Mode');
        $(this).toggleClass('btn-outline-warning btn-outline-primary');
        renderTable();
    });
    
    $('#processAllBtn').on('click', function() {
        const selectedEmployees = payrollData.filter(item => item.selected);
        
        if (selectedEmployees.length === 0) {
            showAlert('Please select at least one employee to process', 'warning');
            return;
        }
        
        const payrollMonth = $('#payrollMonth').val();
        if (!payrollMonth) {
            showAlert('Please select payroll month', 'danger');
            return;
        }
        
        const workingDays = $('#workingDays').val();
        
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');
        
        $.ajax({
            url: "{{ route('payroll.bulk.process') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                employees: selectedEmployees,
                payroll_month: payrollMonth,
                working_days: workingDays
            },
            success: function(response) {
                if (response.success) {
                    showAlert(`Successfully processed ${response.processed_count} employees!`, 'success');
                    setTimeout(() => {
                        window.location.href = "{{ route('payroll.history') }}";
                    }, 1500);
                }
            },
            error: function(xhr) {
                showAlert('Error processing payroll: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
                $('#processAllBtn').prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Process Selected');
            }
        });
    });
    
    function formatNumber(value) {
        const num = parseFloat(value) || 0;
        return 'AED ' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    function showAlert(message, type) {
        const alertDiv = $(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        $('.container-fluid').prepend(alertDiv);
        setTimeout(() => alertDiv.fadeOut(), 5000);
    }
});
</script>
@endpush

<style>
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
    }
    
    .edit-input {
        min-width: 100px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table th, .table td {
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
        white-space: nowrap;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .card {
        border-radius: 1rem;
        overflow: hidden;
        border: none;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    
    @media (max-width: 768px) {
        .table th, .table td {
            white-space: nowrap;
        }
    }
</style>
@endsection