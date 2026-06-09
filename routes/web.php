<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AdvanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Auth\LoginController;

// ── Authentication ────────────────────────────────────────────────────────────

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// ── Authenticated Routes ──────────────────────────────────────────────────────
// Company Master Routes
Route::prefix('master')->group(function () {
    // Department routes
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/data', [DepartmentController::class, 'getData'])->name('departments.data');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Designation routes
    Route::get('/designations', [DesignationController::class, 'index'])->name('designations.index');
    Route::get('/designations/data', [DesignationController::class, 'getData'])->name('designations.data');
    Route::get('/designations/create', [DesignationController::class, 'create'])->name('designations.create');
    Route::post('/designations', [DesignationController::class, 'store'])->name('designations.store');
    Route::get('/designations/{id}/edit', [DesignationController::class, 'edit'])->name('designations.edit');
    Route::put('/designations/{id}', [DesignationController::class, 'update'])->name('designations.update');
    Route::delete('/designations/{id}', [DesignationController::class, 'destroy'])->name('designations.destroy');
    Route::patch('/designations/{id}/status', [DesignationController::class, 'updateStatus'])->name('designations.status');
});


Route::prefix('company')->middleware(['auth'])->group(function () {
    Route::get('/profile', [CompanyController::class, 'index'])->name('company.profile');
    Route::put('/profile', [CompanyController::class, 'updateProfile'])->name('company.profile.update');
    Route::get('companies-data', [CompanyController::class, 'getData'])->name('companies.data');
    // Departmentss
    Route::get('/departments', [DepartmentController::class, 'index'])->name('company.departments');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('company.departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('company.departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('company.departments.destroy');

    // Designations
    Route::get('/designations', [DesignationController::class, 'index'])->name('company.designations');
    Route::post('/designations', [DesignationController::class, 'store'])->name('company.designations.store');
    Route::put('/designations/{designation}', [DesignationController::class, 'update'])->name('company.designations.update');
    Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->name('company.designations.destroy');

});


Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/activity-logs', [SettingsController::class, 'activityLogs'])->name('activity-logs');

    // Employee documents for all employees (admin/super_admin)
    Route::get('/employee-documents', [SettingsController::class, 'employeeDocuments'])
        ->name('employee-documents');
    Route::get('/employee-documents/{document}/view', [SettingsController::class, 'viewEmployeeDocument'])
        ->name('employee-documents.view');
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // AI (Gemini) - Dashboard summary + latest slip explanation
    Route::post('/ai/dashboard/summary', [\App\Http\Controllers\AI\DashboardAIController::class, 'aiSummary'])
        ->name('ai.dashboard.summary');
    Route::post('/ai/dashboard/latest-slip', [\App\Http\Controllers\AI\DashboardAIController::class, 'explainLatestSlip'])
        ->name('ai.dashboard.latest-slip');
    Route::resource('users', UserController::class);
Route::resource('companies', CompanyController::class);

// Company documents (AJAX)
Route::get('/companies/{company}/documents', [CompanyController::class, 'documents'])
    ->name('companies.documents');
Route::get('/company-documents/{document}/file', [CompanyController::class, 'documentFile'])
    ->name('companies.documents.file');
Route::resource('vehicles', VehicleController::class);


    // ── Employees ─────────────────────────────────────────────────────────────
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        // Add this route
        Route::get('/employees/ajax', [EmployeeController::class, 'ajaxEmployees'])->name('ajax');

        // Route::get('/ajax', [EmployeeController::class, 'ajaxEmployees'])->name('ajax');
        // Salary setup
        Route::get('/{employee}/salary', [EmployeeController::class, 'salarySetup'])->name('salary');
        Route::post('/{employee}/salary', [EmployeeController::class, 'saveSalarySetup'])->name('salary.save');

        // Documents
        Route::post('/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('documents.upload');

        // Dependent dropdown (Designations by Department)
        Route::get('/designations/{department}', [EmployeeController::class, 'designationsByDepartment'])
            ->name('designations.byDepartment');

        // Import
        Route::get('/import/form', [EmployeeController::class, 'showImport'])->name('import.form');
        Route::get('/import/template', [EmployeeController::class, 'downloadImportTemplate'])->name('import.template');
        Route::post('/import/store', [EmployeeController::class, 'import'])->name('import.store');
    });

    // ── Payroll ───────────────────────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/employees/by-company', [PayrollController::class, 'getEmployeesByCompany'])->name('employees.by.company');
        Route::get('/employee-defaults/{id}', [PayrollController::class, 'employeeDefaults'])->name('employee-defaults');
        Route::get('/process', [PayrollController::class, 'processForm'])->name('process');
        Route::post('/calculate', [PayrollController::class, 'calculate'])->name('calculate');
        Route::post('/preview-breakdown', [PayrollController::class, 'previewBreakdown'])->name('preview.breakdown');
        Route::get('/bulk', [PayrollController::class, 'bulkForm'])->name('bulk');
        Route::post('/bulk', [PayrollController::class, 'bulkProcess'])->name('bulk.process');
        Route::get('/custom-payment', [PayrollController::class, 'customPaymentForm'])->name('custom-payment');
        Route::post('/custom-payment', [PayrollController::class, 'customPayment'])->name('custom-payment.store');
        Route::get('/history', [PayrollController::class, 'history'])->name('history');
        Route::post('/{record}/status', [PayrollController::class, 'updateStatus'])->name('status');
        Route::delete('/{record}', [PayrollController::class, 'destroy'])->name('destroy');
        Route::get('/{record}/slip', [PayrollController::class, 'salarySlip'])->name('slip');

        // AI (Gemini) - Salary slip explanation
        Route::post('/ai/explain-slip', [\App\Http\Controllers\AI\PayrollAIController::class, 'explainSalarySlip'])
            ->name('ai.explain-slip');

        Route::get('/reports', [PayrollController::class, 'reports'])->name('reports');
        Route::get('/export/excel', [PayrollController::class, 'exportExcel'])->name('export.excel');
        Route::get('/wps', [PayrollController::class, 'wpsReport'])->name('wps');
        Route::get('/wps/export', [PayrollController::class, 'exportWPS'])->name('wps.export');
        Route::get('/download-template', [PayrollController::class, 'downloadTemplate'])->name('download-template');
    });

    // ── Advances ──────────────────────────────────────────────────────────────
    Route::resource('advances', AdvanceController::class)->except(['edit', 'update']);
    Route::get('/advances/{advance}/receipt', [AdvanceController::class, 'receipt'])->name('advances.receipt');

    // ── Settings & Users (Admin only) ─────────────────────────────────────────
    Route::middleware(['can:admin'])->group(function () {

    });
});
