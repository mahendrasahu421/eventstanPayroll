<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceRecovery extends Model
{
    protected $fillable = ['advance_payment_id', 'payroll_record_id', 'amount', 'recovery_month'];

    public function advance()
    {
        return $this->belongsTo(AdvancePayment::class, 'advance_payment_id');
    }

    public function payrollRecord()
    {
        return $this->belongsTo(PayrollRecord::class);
    }
}
