<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\AdvancePayment;
use App\Models\EmployeeDocument;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = now()->format('Y-m');

        // KPIs
        $stats = [
            'total_employees'    => Employee::active()->count(),
            'total_departments'  => \App\Models\Department::where('is_active', true)->count(),
            'payroll_processed'  => PayrollRecord::forMonth($currentMonth)->count(),
            'payroll_pending'    => Employee::active()->count() - PayrollRecord::forMonth($currentMonth)->whereIn('status', ['processed', 'approved', 'paid'])->count(),
            'monthly_gross'      => PayrollRecord::forMonth($currentMonth)->sum('gross_salary'),
            'monthly_net'        => PayrollRecord::forMonth($currentMonth)->sum('net_salary'),
            'active_advances'    => AdvancePayment::where('status', 'active')->sum('pending_amount'),
        ];

        // Document expiry alerts (next 30 days)
        $expiringDocs = EmployeeDocument::with('employee')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Last 6 months payroll trend
        $trend = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i)->format('Y-m');
            return [
                'month' => now()->subMonths($i)->format('M Y'),
                'total' => PayrollRecord::forMonth($month)->sum('net_salary'),
            ];
        });

        // Recent activity
        $recentPayroll = PayrollRecord::with('employee')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'expiringDocs', 'trend', 'recentPayroll'));
    }
}
