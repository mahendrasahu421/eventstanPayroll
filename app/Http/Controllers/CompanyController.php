<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

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
            'company_code',
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'logo',
            'currency',
            'currency_symbol',
            'working_days_per_month',
            'is_active',
            'created_at'
        ]);

        return DataTables::of($companies)
            ->addColumn('logo', function ($company) {
                if (!empty($company->logo) && Storage::disk('public')->exists($company->logo)) {
                    $url = Storage::disk('public')->url($company->logo);
                    return '<img src="' . $url . '" alt="' . $company->company_name . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">';
                }

                if ($company->logo_url) {
                    return '<img src="' . $company->logo_url . '" alt="' . $company->company_name . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">';
                }

                return '<div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-building text-secondary"></i></div>';
            })
            ->addColumn('currency', function ($company) {
                return '<span class="badge bg-info">' . $company->currency_symbol . ' ' . $company->currency . '</span>';
            })
            ->addColumn('status', function ($company) {
                if ($company->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('company_address', function ($company) {
                return Str::limit($company->company_address, 30) ?? '—';
            })
            ->addColumn('created_at', function ($company) {
                return $company->created_at->format('d/m/y');
            })
            ->addColumn('actions', function ($company) {
                return '<div class="btn-group" role="group">
                    <a href="' . route('companies.show', $company) . '" class="btn btn-sm btn-info" title="View"><i class="bi bi-eye"></i></a>
                    <a href="' . route('companies.edit', $company) . '" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete(' . $company->id . ', \' ' . $company->company_name . '\')"><i class="bi bi-trash"></i></button>
                </div>';
            })
            ->rawColumns(['logo', 'currency', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_code' => 'required|digits:13|unique:companies,company_code',
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:5',
            'working_days_per_month' => 'nullable|integer|min:1|max:31',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->except('logo', 'documents');

        $data['currency'] = $data['currency'] ?? 'AED';
        $data['currency_symbol'] = $data['currency_symbol'] ?? 'د.إ';
        $data['working_days_per_month'] = $data['working_days_per_month'] ?? 30;
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('company-logos', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        $company = Company::create($data);

        // Save company documents
        $documents = $request->input('documents', []);
        if (is_array($documents)) {
            foreach ($documents as $index => $docInput) {
                $label = $docInput['label'] ?? null;
                $expiry = $docInput['expiry_date'] ?? null;

                /** @var \Illuminate\Http\UploadedFile|null $uploadedFile */
                $uploadedFile = $request->file("documents.$index.file");

                if (!$uploadedFile || !$label) {
                    continue;
                }

                $storedPath = $uploadedFile->storeAs(
                    'company-documents/' . $company->id,
                    time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension(),
                    'public'
                );

                CompanyDocument::create([
                    'company_id' => $company->id,
                    'label' => $label,
                    'file_path' => $storedPath,
                    'expiry_date' => $expiry,
                ]);
            }
        }

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
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
            'company_code' => 'required|digits:13|unique:companies,company_code,' . $company->id,
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

        if ($request->has('payroll_settings') && $request->payroll_settings) {
            $data['payroll_settings'] = json_decode($request->payroll_settings, true);
        }

        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }

            $logo = $request->file('logo');
            $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('company-logos', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        $company->update($data);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('settings.companies.index')->with('success', 'Company deleted successfully.');
    }

    public function documents(Company $company)
    {
        $docs = $company->documents()->orderBy('expiry_date')->get()->map(function (CompanyDocument $doc) {
            return [
                'id' => $doc->id,
                'label' => $doc->label,
                'expiry_date' => $doc->expiry_date ? $doc->expiry_date->format('d/m/y') : null,
                'file_url' => $doc->file_path ? route('companies.documents.file', $doc) : null,
                'file_extension' => $doc->file_path ? strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : null,
            ];
        });

        return response()->json(['data' => $docs]);
    }

    public function documentFile(CompanyDocument $document)
    {
        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);

        return Response::file(Storage::disk('public')->path($document->file_path));
    }
}

