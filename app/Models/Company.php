<?php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;

    public function documents()
    {
        return $this->hasMany(CompanyDocument::class);
    }


    protected $table = 'companies';

    protected $fillable = [
        'company_code',
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'logo',
        'currency',
        'currency_symbol',
        'working_days_per_month',
        'is_active'
    ];

    protected $casts = [
        'working_days_per_month' => 'integer',
        'is_active' => 'boolean'
    ];

    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
