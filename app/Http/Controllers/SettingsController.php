<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{

    public function index()
    {
        $settings = CompanySetting::first() ?? new CompanySetting();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'working_days_per_month' => 'required|integer|min:1|max:31',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = CompanySetting::firstOrNew([]);

        if ($request->hasFile('logo')) {
            if ($settings->logo)
                Storage::disk('public')->delete($settings->logo);
            $validated['logo'] = $request->file('logo')->store('company', 'public');
        }

        $settings->fill($validated)->save();
        ActivityLog::record('settings_updated', 'Company settings updated');

        return back()->with('success', 'Settings saved.');
    }

    public function activityLogs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->orderBy('created_at', 'desc')
            ->paginate(50)->withQueryString();

        return view('settings.activity-logs', compact('logs'));
    }

    public function employeeDocuments(Request $request)
    {
        $baseQuery = EmployeeDocument::query()->with(['employee.department']);

        if ($request->filled('employee_id')) {
            $baseQuery->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $baseQuery->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('type')) {
            $baseQuery->where('document_type', $request->type);
        }

        if ($request->expired === 'yes') {
            $baseQuery->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now()->toDateString());
        } elseif ($request->expired === 'no') {
            $baseQuery->where(function ($q) {
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()->toDateString());
            });
        }

        if ($request->expiring === 'yes') {
            $endDate = now()->addDays(30)->toDateString();
            $baseQuery->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', now()->toDateString())
                ->whereDate('expiry_date', '<=', $endDate);
        } elseif ($request->expiring === 'no') {
            $endDate = now()->addDays(30)->toDateString();
            $baseQuery->where(function ($q) use ($endDate) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '<', now()->toDateString())
                    ->orWhereDate('expiry_date', '>', $endDate);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('document_type', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($emp) use ($search) {
                        $emp->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->ajax() || $request->wantsJson()) {
            $documents = $baseQuery->orderByRaw('expiry_date IS NULL, expiry_date')
                ->get()
                ->map(function (EmployeeDocument $doc) {
                    $employee = $doc->employee;

                    return [
                        'id' => $doc->id,
                        'employee_id' => $doc->employee_id,
                        'employee_name' => $employee?->full_name ?? '-',
                        'employee_code' => $employee?->employee_code ?? '-',
                        'department_name' => $employee?->department?->name ?? '-',
                        'document_type' => $doc->document_type,
                        'document_number' => $doc->document_number ?? '-',
                        'issue_date' => $doc->issue_date?->format('d M Y') ?? '-',
                        'expiry_date' => $doc->expiry_date?->format('d M Y') ?? '-',
                        'status' => $doc->expiry_date ? ($doc->isExpired() ? 'Expired' : ($doc->isExpiringSoon() ? 'Expiring' : 'Valid')) : 'N/A',
                        'file_url' => $doc->file_path ? Storage::url($doc->file_path) : null,
                        'employee_url' => route('employees.show', $employee),
                    ];
                });

            return response()->json(['data' => $documents]);
        }

        $documents = $baseQuery->orderByRaw('expiry_date IS NULL, expiry_date')
            ->paginate(50)
            ->withQueryString();

        $docTypes = EmployeeDocument::query()
            ->select('document_type')
            ->distinct()
            ->orderBy('document_type')
            ->pluck('document_type');

        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $employees = Employee::query()
            ->with('department')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('settings.employee-documents.index', [
            'documents' => $documents,
            'docTypes' => $docTypes,
            'departments' => $departments,
            'employees' => $employees,
        ]);
    }
}
