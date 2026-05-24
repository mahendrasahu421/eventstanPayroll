<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AdvanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Auth\LoginController;

// ── Authentication ────────────────────────────────────────────────────────────

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// ── Authenticated Routes ──────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/activity-logs', [SettingsController::class, 'activityLogs'])->name('activity-logs');

    // Employee documents for all employees (admin/super_admin)
    Route::get('/employee-documents', [SettingsController::class, 'employeeDocuments'])
        ->name('employee-documents');
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Employees ─────────────────────────────────────────────────────────────
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');

        Route::get('/ajax', [EmployeeController::class, 'ajaxEmployees'])->name('ajax');
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
        Route::post('/import/store', [EmployeeController::class, 'import'])->name('import.store');
    });

    // ── Payroll ───────────────────────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
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
        Route::get('/{record}/slip', [PayrollController::class, 'salarySlip'])->name('slip');
        Route::get('/reports', [PayrollController::class, 'reports'])->name('reports');
        Route::get('/export/excel', [PayrollController::class, 'exportExcel'])->name('export.excel');
        Route::get('/wps', [PayrollController::class, 'wpsReport'])->name('wps');
        Route::get('/wps/export', [PayrollController::class, 'exportWPS'])->name('wps.export');
        Route::get('/download-template', [PayrollController::class, 'downloadTemplate'])->name('download-template');
    });

    // ── Advances ──────────────────────────────────────────────────────────────
    Route::resource('advances', AdvanceController::class)->except(['edit', 'update']);

    // ── Settings & Users (Admin only) ─────────────────────────────────────────
    Route::middleware(['can:admin'])->group(function () {
        Route::resource('users', UserController::class);

    });
});
