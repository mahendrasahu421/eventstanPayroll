<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ActivityLog;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Models\Country;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    private function payrollCompanyName(): ?string
    {
        return CompanySetting::query()->value('company_name');
    }

    // Add this new method for AJAX data table
    public function ajaxEmployees(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'salaryStructure']);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Get paginated results
        $perPage = $request->input('per_page', 25);
        $employees = $query->orderBy('first_name')->paginate($perPage);
        
        // Transform data for DataTable
        $data = $employees->map(function($employee) {
            return [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'photo' => $employee->photo,
                'department_name' => $employee->department?->name ?? '-',
                'designation_name' => $employee->designation?->name ?? '-',
                'basic_salary' => $employee->salaryStructure?->basic_salary ?? 0,
                'status' => $employee->status,
                'joining_date' => $employee->joining_date,
            ];
        });
        
        // Generate pagination HTML
        $pagination = $employees->links()->toHtml();
        
        return response()->json([
            'data' => $data,
            'current_page' => $employees->currentPage(),
            'per_page' => $employees->perPage(),
            'total' => $employees->total(),
            'from' => $employees->firstItem(),
            'to' => $employees->lastItem(),
            'pagination' => $pagination,
            'last_page' => $employees->lastPage(),
        ]);
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
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        $designations = Designation::where('is_active', true)->orderBy('name')->get();
        $countries = Country::query()->orderBy('name')->get();
        $companyName = $this->payrollCompanyName();

        return view('employees.create', compact('departments', 'designations', 'countries', 'companyName'));
    }

    public function store(EmployeeRequest $request)
    {
        $validated = $request->validated();
        $employeeData = [
            'employee_code' => Employee::generateEmployeeCode(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'wps_personal_number' => $validated['wps_personal_number'] ?? null,
            'joining_date' => $validated['joining_date'],
            'department_id' => $validated['department_id'],
            'designation_id' => $validated['designation_id'],
            'status' => $validated['status'] ?? 'active',
        ];

        if ($request->hasFile('photo')) {
            $employeeData['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if (!empty($employeeData['country_id'])) {
            $employeeData['nationality'] = Country::query()->whereKey($employeeData['country_id'])->value('name') ?? $employeeData['nationality'];
        }

        $customFields = $validated['custom_fields'] ?? [];
        if (empty($customFields['payroll_company'])) {
            $companyName = CompanySetting::query()->value('company_name');
            $companyName = $this->payrollCompanyName();

            if (!empty($companyName)) {
                $customFields['payroll_company'] = $companyName;
            }
        }

        $employeeData['custom_fields'] = array_merge($customFields, $request->input('dynamic_custom_fields', []));
        $employee = Employee::create($employeeData);
        
        ActivityLog::record('created', "Employee {$employee->full_name} created", $employee);

        $salaryData = [
            'employee_id' => $employee->id,
            'basic_salary' => $validated['basic_salary'] ?? 0,
            'overtime_rate_per_hour' => $validated['overtime_rate_per_hour'] ?? 0,
            'wps_first_transfer_amount' => $validated['wps_first_transfer_amount'] ?? 0,
            'food_deduction' => $validated['food_deduction'] ?? 0,
            'visa_deduction' => 0,
            'insurance_deduction' => $validated['insurance_deduction'] ?? 0,
            'is_active' => true,
            'effective_from' => now(),
        ];
        $employee->salaryStructures()->create($salaryData);

        $visaTotal = (float) ($validated['visa_deduction'] ?? 0);
        $visaInstallments = (int) ($validated['visa_total_installments'] ?? 1);

        if ($visaTotal > 0 && $visaInstallments > 1) {
            $installmentAmount = round($visaTotal / $visaInstallments, 2);
            $visaAdvance = $employee->advances()->create([
                'amount' => $visaTotal,
                'advance_date' => now()->toDateString(),
                'reason' => 'Visa Charges (Installments)',
                'installment_amount' => $installmentAmount,
                'total_installments' => $visaInstallments,
                'paid_installments' => 0,
                'recovered_amount' => 0,
                'pending_amount' => $visaTotal,
                'status' => 'active',
            ]);
            ActivityLog::record('created', "Visa installments created for {$employee->full_name}", $visaAdvance);
        }

        $docTypes = ['passport', 'emirates_id', 'labour_card', 'driving_license'];
        foreach ($docTypes as $type) {
            if (isset($validated['documents'][$type])) {
                $docData = [
                    'employee_id' => $employee->id,
                    'document_type' => $type,
                    'document_number' => $validated['documents'][$type]['number'] ?? null,
                    'issue_date' => null,
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
            'department',
            'designation',
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
        $countries = Country::query()->orderBy('name')->get();
        $companyName = $this->payrollCompanyName();

        return view('employees.edit', compact('employee', 'departments', 'designations', 'countries', 'companyName'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $old = $employee->toArray();
        $data = $request->validated();

        if (!empty($data['country_id'])) {
            $data['nationality'] = Country::query()->whereKey($data['country_id'])->value('name') ?? $data['nationality'];
        }

        $companyName = $this->payrollCompanyName();
        if (empty($data['custom_fields']['payroll_company']) && !empty($companyName)) {
            $data['custom_fields']['payroll_company'] = $companyName;
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo)
                Storage::disk('public')->delete($employee->photo);
            $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update($data);
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
            'insurance_deduction' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $employee->salaryStructures()->update(['is_active' => false, 'effective_to' => now()]);
        $employee->salaryStructures()->create($validated + ['is_active' => true]);

        ActivityLog::record('salary_updated', "Salary structure updated for {$employee->full_name}", $employee);

        return redirect()->route('employees.show', $employee)->with('success', 'Salary structure saved.');
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

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv|max:10240']);

        $import = new EmployeesImport();
        Excel::import($import, $request->file('file'));

        $errors = $import->getErrors();
        ActivityLog::record('bulk_import', 'Bulk employee import executed');

        return redirect()->route('employees.index')
            ->with('success', "Import complete. {$import->getRowCount()} rows processed.")
            ->with('import_errors', $errors);
    }
}
