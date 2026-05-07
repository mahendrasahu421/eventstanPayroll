<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvancePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'amount', 'advance_date', 'reason',
        'installment_amount', 'total_installments', 'paid_installments',
        'recovered_amount', 'pending_amount', 'status', 'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function recoveries()
    {
        return $this->hasMany(AdvanceRecovery::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
