<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_id',
        'nationality',
        'country_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'department_id',
        'designation_id',
        'joining_date',
        'confirmation_date',
        'resignation_date',
        'employment_type',
        'status',
        'bank_name',
        'bank_account_number',
        'iban',
        'wps_personal_number',
        'custom_fields',
        'address',
        'photo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'resignation_date' => 'date',
        'custom_fields' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }


    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class)->where('is_active', true)->latest('effective_from');
    }

    public function salaryStructures()
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function advances()
    {
        return $this->hasMany(AdvancePayment::class);
    }

    public function activeAdvances()
    {
        return $this->hasMany(AdvancePayment::class)->where('status', 'active');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('employee_code', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function generateEmployeeCode(): string
    {
        $last = self::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? ((int) substr($last->employee_code, 3)) + 1 : 1;
        return 'EMP' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function getPendingAdvanceAmount(): float
    {
        return (float) $this->activeAdvances()->sum('pending_amount');
    }

    public function getExpiringDocuments(int $days = 30)
    {
        return $this->documents()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now())
            ->get();
    }
}
