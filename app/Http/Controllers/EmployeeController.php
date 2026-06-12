<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ActivityLog;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Models\Country;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    private function payrollCompanyName(Employee $employee = null): ?string
    {
        return $employee?->company?->name ?? CompanySetting::query()->value('company_name');
    }

    // Add this new method for AJAX data table
    public function ajaxEmployees(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'salaryStructure', 'company'])
            ->orderBy('created_at', 'desc');

        // Apply department filter
        if ($request->has('department') && !blank($request->department)) {
            $query->where('department_id', $request->department);
        }

        // Apply status filter
        if ($request->has('status') && !blank($request->status)) {
            $query->where('status', $request->status);
        }

        // Apply company filter if needed
        if ($request->has('company_id') && !blank($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        return DataTables::of($query)
            ->addColumn('photo', function ($employee) {
                if ($employee->photo) {
                    return '<img src="/storage/' . $employee->photo . '" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
                } else {
                    $initials = substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1);
                    return '<div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:0.8rem;font-weight:600;">' . ($initials ?: 'N/A') . '</div>';
                }
            })
            ->addColumn('employee_details', function ($employee) {
                return '
                    <div class="fw-semibold">' . e($employee->full_name) . '</div>
                    <small class="text-muted">' . e($employee->employee_code) . '</small>
                    ' . ($employee->email ? '<br><small class="text-muted"><i class="bi bi-envelope"></i> ' . e($employee->email) . '</small>' : '') . '
                ';
            })
            ->addColumn('department_name', function ($employee) {
                return $employee->department ? e($employee->department->name) : '-';
            })
            ->addColumn('designation_name', function ($employee) {
                return $employee->designation ? e($employee->designation->name) : '-';
            })
            ->addColumn('basic_salary', function ($employee) {
                $salary = $employee->salaryStructure?->basic_salary ?? 0;
                if ($salary == 0)
                    return '-';
                return number_format($salary, 2) . ' AED';
            })
            ->addColumn('status', function ($employee) {
                if ($employee->status == 'active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('joining_date', function ($employee) {
                if (!$employee->joining_date)
                    return '-';
                $date = new \DateTime($employee->joining_date);
                return $date->format('d-m-y');
            })
            ->addColumn('actions', function ($employee) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('employees.show', $employee->id) . '" class="btn btn-outline-primary" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="' . route('employees.edit', $employee->id) . '" class="btn btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" title="Delete" 
                                onclick="deleteEmployee(' . $employee->id . ', \'' . addslashes($employee->full_name) . '\')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->filter(function ($query) use ($request) {
                // Apply search filter
                if ($request->has('search') && !blank($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            })
            ->rawColumns(['photo', 'employee_details', 'status', 'actions'])
            ->make(true);
    }

    public function designationsByDepartment(Department $department)
    {
        $designations = $department->designations()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'designations' => $designations,
        ]);
    }

    public function index(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('employees.index', compact('departments'));
    }

    public function create()
    {
        $employee = new Employee(); // Empty employee model for the form
        $departments = Department::where('is_active', true)->orderBy('name')->get()->unique('name')->values();
        $designations = Designation::where('is_active', true)->orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $companies = Company::where('is_active', true)->orderBy('company_name')->get();
        $companyName = $this->payrollCompanyName();

        return view('employees.form', compact('employee', 'departments', 'designations', 'countries', 'companies', 'companyName'));
    }

    public function store(EmployeeRequest $request)
    {
        $validated = $request->validated();

        $employeeData = [
            'employee_code' => Employee::generateEmployeeCode(),
            'company_id' => $validated['company_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'wps_personal_number' => $validated['wps_personal_number'] ?? null,
            'joining_date' => $validated['joining_date'],
            'department_id' => $validated['department_id'] ?? null,
            'designation_id' => $validated['designation_id'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ];

        if ($request->hasFile('photo')) {
            $employeeData['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if (!empty($employeeData['country_id'])) {
            $employeeData['nationality'] = Country::query()->whereKey($employeeData['country_id'])->value('name') ?? $employeeData['nationality'];
        }

        $customFields = $validated['custom_fields'] ?? [];

       

        // Remove insurance keys so they won't remain inside custom_fields.
      

        if (empty($customFields['payroll_company'])) {
            $companyName = $this->payrollCompanyName();

            if (!empty($companyName)) {
                $customFields['payroll_company'] = $companyName;
            }
        }

        $dynamicCustomFields = $request->input('dynamic_custom_fields', []);
        $employeeData['custom_fields'] = array_merge($customFields, $dynamicCustomFields);
       

        $employee = Employee::create($employeeData);


        ActivityLog::record('created', "Employee {$employee->full_name} created", $employee);

        $salaryData = [
            'employee_id' => $employee->id,
            'basic_salary' => $validated['basic_salary'] ?? 0,
            'overtime_rate_per_hour' => $validated['overtime_rate_per_hour'] ?? 0,
            'wps_first_transfer_amount' => $validated['wps_first_transfer_amount'] ?? 0,
            'food_deduction' => $validated['food_deduction'] ?? 0,
            'visa_deduction' => $validated['visa_deduction'] ?? 0,
            'insurance_deduction' => $validated['insurance_deduction'] ?? 0,
            'visa_total_installments' => $validated['visa_total_installments'] ?? 1,
            'visa_total_amount' => $validated['visa_deduction'] ?? 0,
            'advance_payment' => $validated['advance_payment'] ?? 0,
            // “Other Deductions” in the UI.
            'other_allowance' => $validated['other_deduction'] ?? $validated['other_allowance'] ?? 0,
            'is_active' => true,
            'effective_from' => now(),
        ];
        $employee->salaryStructures()->create($salaryData);

        // Create Visa installments advance at employee creation time (so it exists immediately)
        $visaTotalAmount = (float) ($validated['visa_deduction'] ?? 0);
        $visaTotalInstallments = (int) ($validated['visa_total_installments'] ?? 1);

        if ($visaTotalAmount > 0 && $visaTotalInstallments > 0) {
            $monthlyInstallment = round($visaTotalAmount / $visaTotalInstallments, 2);

            \App\Models\AdvancePayment::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'reason' => 'Visa Charges (Installments)',
                    'status' => 'active',
                ],
                [
                    'amount' => $visaTotalAmount,
                    'advance_date' => now()->toDateString(),
                    'installment_amount' => $monthlyInstallment,
                    'total_installments' => $visaTotalInstallments,
                    'pending_amount' => $visaTotalAmount,
                ]
            );
        }

        $docTypes = ['passport', 'emirates_id', 'insurance', 'driving_license'];
        foreach ($docTypes as $type) {
            if (isset($validated['documents'][$type])) {
                $docData = [
                    'employee_id' => $employee->id,
                    'document_type' => $type,
                    'document_number' => $validated['documents'][$type]['number'] ?? null,
                    'issue_date' => $validated['documents'][$type]['issue_date'] ?? null,
                ];

                if (isset($validated['documents'][$type]['expiry_date'])) {
                    $docData['expiry_date'] = $validated['documents'][$type]['expiry_date'];
                }

                if ($request->hasFile("documents.{$type}.file")) {
                    $docData['file_path'] = $request->file("documents.{$type}.file")->store("employees/documents/{$employee->id}", 'public');
                }

                $employee->documents()->create($docData);
            }
        }

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully with salary structure and documents.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'company',
            'department',
            'designation',
            'advances', // Load advances fvto get visa installment details
            'salaryStructure',
            'documents',
            'advances.recoveries.payrollRecord',
        ]);

        $companyName = $this->payrollCompanyName();

        return view('employees.show', compact('employee', 'companyName'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        $designations = Designation::where('is_active', true)->orderBy('name')->get();
        $employee->load(['advances', 'documents', 'salaryStructure']); // Ensure required relations are loaded for the form
        $countries = Country::query()->orderBy('name')->get();
        $companies = Company::where('is_active', true)->orderBy('company_name')->get();
        $companyName = $this->payrollCompanyName();

        return view('employees.form', compact('employee', 'departments', 'designations', 'countries', 'companies', 'companyName'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $old = $employee->toArray();
        $validated = $request->validated();

        // Keep nationality as text column
        if (!empty($validated['country_id'])) {
            $validated['nationality'] = Country::query()->whereKey($validated['country_id'])->value('name') ?? $validated['nationality'];
        }

        // Build custom_fields the same way as store() to avoid edit errors / missing rows.
        $fixedCustomFields = $validated['custom_fields'] ?? [];

        // Inject payroll_company if missing.
        $companyName = $this->payrollCompanyName();
        if (empty($fixedCustomFields['payroll_company']) && !empty($companyName)) {
            $fixedCustomFields['payroll_company'] = $companyName;
        }

        // Remove insurance metadata from custom_fields if it exists there (we store insurance as columns).
        foreach (['insurance_provider', 'insurance_policy_number', 'insurance_card_number', 'insurance_start_date', 'insurance_end_date'] as $k) {
            unset($fixedCustomFields[$k]);
        }

        // Convert dynamic_custom_fields array[{name,value},...] into key=>value for custom_fields json.
        $dynamicRows = $request->input('dynamic_custom_fields', []);
        $dynamicCustomFields = [];
        if (is_array($dynamicRows)) {
            foreach ($dynamicRows as $row) {
                if (!is_array($row))
                    continue;
                $name = $row['name'] ?? null;
                $value = $row['value'] ?? null;

                if ($name === null || $name === '') {
                    continue;
                }

                $dynamicCustomFields[$name] = $value;
            }
        }

        $validated['custom_fields'] = array_merge($fixedCustomFields, $dynamicCustomFields);

        // Ensure insurance metadata stored as columns (not custom_fields)
        $validated['insurance_provider'] = $request->input('custom_fields.insurance_provider')
            ?? $request->input('insurance_provider')
            ?? $employee->insurance_provider;
        $validated['insurance_policy_number'] = $request->input('custom_fields.insurance_policy_number')
            ?? $request->input('insurance_policy_number')
            ?? $employee->insurance_policy_number;
        $validated['insurance_card_number'] = $request->input('custom_fields.insurance_card_number')
            ?? $request->input('insurance_card_number')
            ?? $employee->insurance_card_number;
        $validated['insurance_start_date'] = $request->input('custom_fields.insurance_start_date')
            ?? $request->input('insurance_start_date')
            ?? $employee->insurance_start_date;
        $validated['insurance_end_date'] = $request->input('custom_fields.insurance_end_date')
            ?? $request->input('insurance_end_date')
            ?? $employee->insurance_end_date;


        // Photo
        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update($validated);

        // ✅ Keep salary structure in sync with Insurance shown in employee form.
        // Your payroll calculation uses salaryStructure->insurance_deduction.
        if (array_key_exists('insurance_deduction', $validated)) {
            $employee->salaryStructures()->update(['is_active' => false, 'effective_to' => now()]);
            $employee->salaryStructures()->create([
                'employee_id' => $employee->id,
                'basic_salary' => $employee->salaryStructure?->basic_salary ?? ($validated['basic_salary'] ?? 0),
                'housing_allowance' => $employee->salaryStructure?->housing_allowance ?? 0,
                'transport_allowance' => $employee->salaryStructure?->transport_allowance ?? 0,
                'medical_allowance' => $employee->salaryStructure?->medical_allowance ?? 0,
                'other_allowance' => $employee->salaryStructure?->other_allowance ?? 0,
                'increment_value' => $employee->salaryStructure?->increment_value ?? 0,
                'overtime_rate_per_hour' => $employee->salaryStructure?->overtime_rate_per_hour ?? 0,
                'wps_first_transfer_amount' => $employee->salaryStructure?->wps_first_transfer_amount ?? 0,
                'food_deduction' => $employee->salaryStructure?->food_deduction ?? 0,
                'visa_deduction' => $employee->salaryStructure?->visa_deduction ?? 0,
                'insurance_deduction' => (float) $validated['insurance_deduction'],
                'advance_payment' => $employee->salaryStructure?->advance_payment ?? 0,
                'is_active' => true,
                'effective_from' => now(),
            ]);
        }

        // Update Documents (Passport, Emirates ID, etc.)
        if (isset($validated['documents'])) {
            foreach ($validated['documents'] as $type => $docData) {
                if (empty($docData['number']) && empty($docData['expiry_date']) && !$request->hasFile("documents.{$type}.file")) {
                    continue;
                }

                $updatePayload = [
                    'document_number' => $docData['number'] ?? null,
                    'issue_date' => $docData['issue_date'] ?? null,
                    'expiry_date' => $docData['expiry_date'] ?? null,
                ];

                if ($request->hasFile("documents.{$type}.file")) {
                    $updatePayload['file_path'] = $request->file("documents.{$type}.file")->store("employees/documents/{$employee->id}", 'public');
                }

                $employee->documents()->updateOrCreate(
                    ['document_type' => $type],
                    $updatePayload
                );
            }
        }

        // ✅ Sync Insurance columns -> documents tab (document_type='insurance')
        $existingInsuranceDoc = $employee->documents()->where('document_type', 'insurance')->first();

        $employee->documents()->updateOrCreate(
            ['document_type' => 'insurance', 'employee_id' => $employee->id],
            [
                'document_number' => $employee->insurance_policy_number,
                'issue_date' => $employee->insurance_start_date,
                'expiry_date' => $employee->insurance_end_date,
                'file_path' => $existingInsuranceDoc?->file_path,
                'notes' => $employee->insurance_provider,
            ]
        );


        ActivityLog::record('updated', "Employee {$employee->full_name} updated", $employee, $old, $employee->toArray());

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
            ActivityLog::record('deleted', "Employee {$employee->full_name} deleted", $employee);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Employee deleted successfully']);
            }

            return redirect()->route('employees.index')->with('success', 'Employee deleted.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting employee'], 500);
            }
            return back()->with('error', 'Error deleting employee');
        }
    }

    public function salarySetup(Employee $employee)
    {
        $salary = $employee->salaryStructure;
        return view('employees.salary-setup', compact('employee', 'salary'));
    }

    public function saveSalarySetup(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'overtime_rate_per_hour' => 'nullable|numeric|min:0',
            'wps_first_transfer_amount' => 'nullable|numeric|min:0',
            'food_deduction' => 'nullable|numeric|min:0',
            'visa_deduction' => 'nullable|numeric|min:0',
            'visa_total_installments' => 'nullable|integer|min:1',
            'insurance_deduction' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $employee->salaryStructures()->update(['is_active' => false, 'effective_to' => now()]);
            $structure = $employee->salaryStructures()->create($validated + ['is_active' => true]);

            // Sync Visa installment if visa_deduction is updated
            if (isset($validated['visa_deduction']) && $validated['visa_deduction'] > 0) {
                $totalInstallments = $validated['visa_total_installments'] ?? 1;
                $monthlyInstallment = round($validated['visa_deduction'] / $totalInstallments, 2);

                // Update or create the "Visa Charges (Installments)" advance record
                \App\Models\AdvancePayment::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'reason' => 'Visa Charges (Installments)',
                        'status' => 'active'
                    ],
                    [
                        'amount' => $validated['visa_deduction'],
                        'advance_date' => now()->toDateString(),
                        'installment_amount' => $monthlyInstallment,
                        'total_installments' => $totalInstallments,
                        'pending_amount' => $validated['visa_deduction'],
                    ]
                );
            }

            ActivityLog::record('salary_updated', "Salary structure updated for {$employee->full_name}", $employee);
            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('employees.show', $employee)->with('success', 'Salary structure saved.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error updating salary structure: ' . $e->getMessage());
        }
    }

    public function uploadDocument(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'document_type' => 'required|string',
            'document_number' => 'nullable|string|max:100',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store("employees/documents/{$employee->id}", 'public');
        }

        $employee->documents()->create($validated);

        return back()->with('success', 'Document uploaded.');
    }

    public function showImport()
    {
        return view('employees.import');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            // Employee
            'employee_id',
            'employee_name',
            'dob',
            'passport_number',
            'passport_expiry',
            'visa_from',
            'visa_expiry',
            'wps_id',
            'nationality',
            'company',
            'insurance_effective',
            'insurance_expiry',

            // Salary Structure
            'basic_salary',
            'increment_value',
            'overtime_rate_per_hour',
            'wps_first_transfer_amount',
            'food_deduction',
            'visa_deduction',
            'total_installments',
            'advance_payment',
            'insurance_deduction',
            'other_deductions',
        ];

        $sampleRow = [
            // Employee
            'EMP-0001',
            'John Doe',
            now()->subYears(30)->format('Y-m-d'),
            'P12345678',
            now()->addYears(2)->format('Y-m-d'),
            '784-1234-5678901-1', // emirates id number
            now()->addYears(1)->format('Y-m-d'), // emirates id expiry
            '12345678901234',
            'United Arab Emirates',
            'Eventstan LLC',
            now()->subMonths(2)->format('Y-m-d'),
            now()->addYears(1)->format('Y-m-d'),

            // Salary Structure
            '5000',
            '500',
            '25',
            '5000',
            '300',
            '500',
            '12',
            '200',
            '100',
            '50',
        ];

        // Sanity check — development mein uncomment karein
        // throw_if(count($headers) !== count($sampleRow), \Exception::class,
        //     'Header count (' . count($headers) . ') and sample row count (' . count($sampleRow) . ') do not match!'
        // );

        $callback = function () use ($headers, $sampleRow) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($file, $headers);
            fputcsv($file, $sampleRow);
            fclose($file);
        };

        return response()->streamDownload($callback, 'employee-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:10240',
        ]);

        $import = new EmployeesImport();
        Excel::import($import, $request->file('file'));

        $errors = $import->getErrors();
        ActivityLog::record('bulk_import', 'Bulk employee import executed');

        return redirect()->route('employees.index')
            ->with('success', "Import complete. {$import->getRowCount()} rows processed.")
            ->with('import_errors', $errors);
    }
}