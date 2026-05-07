# PayRoll Manager — Laravel Application

A complete **Standard Payroll Management System** built with Laravel, based on the project requirements.

---

## Features

- Multi-role authentication (Super Admin, Admin, Accounts Staff, HR Staff)
- Employee Master Management with custom fields
- Salary Structure Setup (basic, allowances, deductions, overtime)
- Single & Bulk Payroll Processing
- WPS (Wage Protection System) report & SIF file export
- Advance Payment management with installment recovery
- PDF Salary Slips
- Excel/CSV export for all reports
- Document management with expiry alerts
- Bulk employee import via Excel/CSV
- Activity audit logs
- Company settings & branding
- Responsive Bootstrap 5 UI

---

## Tech Stack

| Layer          | Technology                            |
|----------------|---------------------------------------|
| Backend        | Laravel 11                            |
| Frontend       | Blade + Bootstrap 5                   |
| Database       | MySQL 8+                              |
| PDF Generation | barryvdh/laravel-dompdf               |
| Excel Export   | maatwebsite/excel                     |
| Authentication | Laravel Auth                          |

---

## Installation

### 1. Clone / Extract the project
```bash
cd /var/www
git clone <repo> payroll-manager
cd payroll-manager
```

### 2. Install dependencies
```bash
composer install
npm install && npm run build
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials:
```
DB_DATABASE=payroll_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 4. Run migrations and seed
```bash
php artisan migrate --seed
```

### 5. Storage link
```bash
php artisan storage:link
```

### 6. Start server
```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## Default Login Credentials

| Role            | Email                    | Password   |
|-----------------|--------------------------|------------|
| Super Admin     | admin@payroll.com        | password   |
| HR Staff        | hr@payroll.com           | password   |
| Accounts Staff  | accounts@payroll.com     | password   |

> **Change these immediately after first login!**

---

## Module Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── DashboardController.php
│   │   ├── EmployeeController.php      # Employee CRUD + salary + documents + import
│   │   ├── PayrollController.php       # Process, bulk, history, reports, WPS, slip
│   │   ├── AdvanceController.php       # Advance payments + recovery tracking
│   │   ├── UserController.php          # User management
│   │   └── SettingsController.php      # Company settings + activity logs
│   └── Requests/
│       └── EmployeeRequest.php
├── Models/
│   ├── User.php
│   ├── Employee.php
│   ├── Department.php
│   ├── Designation.php
│   ├── SalaryStructure.php
│   ├── EmployeeDocument.php
│   ├── PayrollRecord.php
│   ├── AdvancePayment.php
│   ├── AdvanceRecovery.php
│   ├── CompanySetting.php
│   └── ActivityLog.php
├── Services/
│   └── PayrollService.php              # Core payroll calculation logic
├── Imports/
│   └── EmployeesImport.php             # Bulk Excel import
└── Exports/
    └── PayrollExport.php               # Excel export

database/
├── migrations/
│   ├── 000_create_users_table.php
│   ├── 001_create_employees_table.php
│   └── 002_create_payroll_tables.php
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── layouts/app.blade.php
├── dashboard.blade.php
└── payroll/salary-slip.blade.php
```

---

## Payroll Calculation Logic

```
Gross Salary = (Basic + Allowances) × (Present Days / Working Days) + Overtime

Net Salary = Gross Salary − Total Deductions

WPS 2nd Transfer = Net Salary − WPS 1st Transfer
```

Advance deductions are automatically calculated from active advances and applied monthly.

---

## WPS Export

The system generates a **SIF (Salary Information File)** compatible with UAE WPS requirements:
```
EH|YYYY-MM|<count>|<total>
ED|<wps_personal_number>|<IBAN>|<net_salary>
```

---

## Role Permissions

| Feature               | Super Admin | Admin | Accounts | HR |
|-----------------------|:-----------:|:-----:|:--------:|:--:|
| Manage Employees      | ✓           | ✓     |          | ✓  |
| Process Payroll       | ✓           | ✓     |          |    |
| Approve Payroll       | ✓           |       |          |    |
| View Reports          | ✓           | ✓     | ✓        |    |
| Export WPS            | ✓           | ✓     |          |    |
| Manage Users          | ✓           |       |          |    |
| Company Settings      | ✓           | ✓     |          |    |

---

## Required Packages

Add to `composer.json` and run `composer install`:
```json
"maatwebsite/excel": "^3.1",
"barryvdh/laravel-dompdf": "^2.2"
```

Publish configs:
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```
