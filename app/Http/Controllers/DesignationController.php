<?php
// app/Http/Controllers/DesignationController.php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    // List page with DataTable
    public function index()
    {
        return view('designations.index');
    }

    // AJAX DataTable data
    public function getData(Request $request)
    {
        $designations = Designation::with('department')
            ->select('designations.*')
            ->orderBy('created_at', 'desc');
        
        return DataTables::of($designations)
            ->addColumn('department_name', function($designation) {
                return $designation->department ? $designation->department->name : 'N/A';
            })
            ->addColumn('status', function($designation) {
                if ($designation->is_active) {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function($designation) {
                return '
                    <div class="btn-group" role="group">
                        <a href="'.route('designations.edit', $designation->id).'" class="btn btn-sm btn-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                                onclick="deleteDesignation('.$designation->id.', \''.$designation->name.'\')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    // Create page
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('designations.create', compact('departments'));
    }

    // Store new designation
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255|unique:designations,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Designation::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    // Edit page
    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('designations.edit', compact('designation', 'departments'));
    }

    // Update designation
    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255|unique:designations,name,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $designation->update([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false
        ]);

        return redirect()->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    // Delete designation
    public function destroy($id)
    {
        $designation = Designation::findOrFail($id);
        
        if ($designation->employees()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete designation with associated employees'
            ], 400);
        }
        
        $designation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Designation deleted successfully'
        ]);
    }

    // Update status via AJAX
    public function updateStatus(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);
        $designation->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Designation status updated successfully'
        ]);
    }
}