<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_code',
        'plate_number',
        'vehicle_name',
        'vehicle_type',
        'registration_expiry_date',
        'insurance_expiry_date',
        'permit_expiry_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'registration_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'permit_expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function expiryStatus(?string $field): string
    {
        $date = $field ? $this->{$field} : null;

        if (!$date) {
            return 'missing';
        }

        if ($date->isPast()) {
            return 'expired';
        }

        if ($date->lte(now()->addMonth())) {
            return 'expiring';
        }

        return 'valid';
    }
}
