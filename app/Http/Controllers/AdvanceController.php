<?php

namespace App\Http\Controllers;

use App\Models\AdvancePayment;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AdvanceController extends Controller
{
    public function index(Request $request)
    {
        $advances = AdvancePayment::with('employee')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->employee, fn($q) => $q->where('employee_id', $request->employee))
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        $employees = Employee::active()->orderBy('first_name')->get();
        return view('advances.index', compact('advances', 'employees'));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('first_name')->get();
        return view('advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'amount'              => 'required|numeric|min:1',
            'advance_date'        => 'required|date',
            'reason'              => 'nullable|string',
            'installment_amount'  => 'required|numeric|min:1',
            'total_installments'  => 'required|integer|min:1',
        ]);


        $validated['pending_amount'] = $validated['amount'];
        $validated['created_by']     = auth()->id();

        $advance = AdvancePayment::create($validated);
        $advance->load(['employee.department', 'createdBy']);

        // Auto-generate receipt PDF after saving advance (no upload from user)
        $receiptFileName = 'advance-receipt-' . $advance->id . '.pdf';
        $receiptRelativePath = 'advance-receipts/' . $receiptFileName;
        $receiptStoragePath = storage_path('app/public/' . $receiptRelativePath);

        try {
            $advance->loadMissing('employee.department');

            $pdf = \PDF::loadView('advances.receipt', compact('advance'))
                ->setPaper('a4', 'portrait');


            // Ensure directory exists
            if (!is_dir(dirname($receiptStoragePath))) {
                mkdir(dirname($receiptStoragePath), 0775, true);
            }

            $pdf->save($receiptStoragePath);
            $validatedReceiptPath = $receiptRelativePath;
        } catch (\Throwable $e) {
            // If PDF generation fails for any reason, keep advance saved without blocking.
            $validatedReceiptPath = null;
        }

        if ($validatedReceiptPath) {
            $advance->update(['receipt_path' => $validatedReceiptPath]);
        }

        ActivityLog::record('advance_created', "Advance of {$advance->amount} created for employee #{$advance->employee_id}", $advance);

        return redirect()->route('advances.show', $advance)->with(
            'success',
            $validatedReceiptPath ? 'Advance saved. Receipt generated.' : 'Advance payment recorded.'
        );





    }

    public function show(AdvancePayment $advance)
    {
        $advance->load(['employee', 'recoveries.payrollRecord']);
        return view('advances.show', compact('advance'));
    }

    public function receipt(AdvancePayment $advance)
    {
        abort_unless($advance->receipt_path && Storage::disk('public')->exists($advance->receipt_path), 404);

        return Response::file(Storage::disk('public')->path($advance->receipt_path));
    }

    public function destroy(AdvancePayment $advance)
    {
        $advance->update(['status' => 'cancelled']);
        ActivityLog::record('advance_cancelled', "Advance #{$advance->id} cancelled", $advance);
        return redirect()->route('advances.index')->with('success', 'Advance cancelled.');
    }
}
