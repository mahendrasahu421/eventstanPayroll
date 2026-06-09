<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'medical_allowance',
        'other_allowance',
        'increment_value',
        'overtime_rate_per_hour',
        'wps_first_transfer_amount',
        'food_deduction',
        'visa_deduction',
        'insurance_deduction',
        'advance_payment',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->housing_allowance +
            $this->transport_allowance + $this->medical_allowance +
            $this->other_allowance;
    }
}
