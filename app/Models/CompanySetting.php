<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'company_email', 'company_phone', 'company_address',
        'logo', 'currency', 'currency_symbol', 'working_days_per_month', 'payroll_settings',
    ];

    protected $casts = [
        'payroll_settings' => 'array',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
