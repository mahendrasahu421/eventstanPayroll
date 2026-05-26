<?php
// app/Http/Controllers/CompanyController.php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('created_at', 'desc')->paginate(20);
        return view('settings.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('settings.companies.create');
    }
public function getData(Request $request)
    {
        $companies = Company::select([
            'id',
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'logo',
            'currency',
            'currency_symbol',
            'working_days_per_month',
            'overtime_rate',
            'is_active',
            'created_at'
        ]);

        return DataTables::of($companies)
            ->addColumn('logo', function($company) {
                if ($company->logo_url) {
                    return '<img src="'.$company->logo_url.'" alt="'.$company->company_name.'" 
                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">';
                } else {
                    return '<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-building text-secondary"></i>
                            </div>';
                }
            })
            ->addColumn('currency', function($company) {
                return '<span class="badge bg-info">'.$company->currency_symbol.' '.$company->currency.'</span>';
            })
            ->addColumn('overtime_rate', function($company) {
                if ($company->overtime_rate) {
                    return '<span class="badge bg-warning">
                                '.$company->currency_symbol.' '.number_format($company->overtime_rate, 2).'/hr
                            </span>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->addColumn('status', function($company) {
                if ($company->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('company_address', function($company) {
                return Str::limit($company->company_address, 30) ?? '—';
            })
            ->addColumn('created_at', function($company) {
                return $company->created_at->format('d M Y');
            })
            ->addColumn('actions', function($company) {
                return '
                    <div class="btn-group" role="group">
                        <a href="'.route('companies.show', $company).'" class="btn btn-sm btn-info" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="'.route('companies.edit', $company).'" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                                onclick="confirmDelete('.$company->id.', \''.$company->company_name.'\')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['logo', 'currency', 'overtime_rate', 'status', 'actions'])
            ->make(true);
    }

  public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'company_name' => 'required|string|max:255|unique:companies,company_name',
        'company_email' => 'nullable|email|max:255',
        'company_phone' => 'nullable|string|max:20',
        'company_address' => 'nullable|string',
        'currency' => 'nullable|string|max:3',
        'currency_symbol' => 'nullable|string|max:5',
        'working_days_per_month' => 'nullable|integer|min:1|max:31',
        'overtime_rate' => 'nullable|numeric|min:0', // No default, user can enter any amount
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $data = $request->except('logo');
    
    // Set defaults
    $data['currency'] = $data['currency'] ?? 'AED';
    $data['currency_symbol'] = $data['currency_symbol'] ?? 'د.إ';
    $data['working_days_per_month'] = $data['working_days_per_month'] ?? 26;
    // overtime_rate can be null or user entered value
    $data['is_active'] = $request->has('is_active') ? true : false;
    
    // Handle logo upload
    if ($request->hasFile('logo')) {
        $logo = $request->file('logo');
        $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
        $logoPath = $logo->storeAs('company-logos', $logoName, 'public');
        $data['logo'] = $logoPath;
    }

    Company::create($data);

    return redirect()->route('companies.index')
        ->with('success', 'Company created successfully.');
}    public function show(Company $company)
    {
        return view('settings.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('settings.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255|unique:companies,company_name,' . $company->id,
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:5',
            'working_days_per_month' => 'nullable|integer|min:1|max:31',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'payroll_settings' => 'nullable|json',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->except('logo', 'payroll_settings');
        
        // Handle payroll settings as JSON
        if ($request->has('payroll_settings') && $request->payroll_settings) {
            $data['payroll_settings'] = json_decode($request->payroll_settings, true);
        }
        
        // Handle is_active checkbox
        $data['is_active'] = $request->has('is_active');
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('company-logos', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        $company->update($data);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        // Delete logo if exists
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }
        
        $company->delete();

        return redirect()->route('settings.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}