<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id', 'payroll_month', 'working_days', 'present_days',
        'leave_days', 'overtime_hours',
        'basic_salary', 'housing_allowance', 'transport_allowance',
        'medical_allowance', 'other_allowance', 'overtime_amount', 'gross_salary',
        'food_deduction', 'visa_deduction', 'insurance_deduction',
        'advance_deduction', 'other_deduction', 'total_deductions',
        'net_salary', 'wps_first_transfer', 'wps_second_transfer',
        'status', 'remarks', 'processed_by', 'processed_at', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function advanceRecoveries()
    {
        return $this->hasMany(AdvanceRecovery::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForMonth($query, string $month)
    {
        return $query->where('payroll_month', $month);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getMonthLabelAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->payroll_month)->format('F Y');
    }
}
