<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active', 'phone', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool  { return $this->role === 'super_admin'; }
    public function isAdmin(): bool       { return in_array($this->role, ['super_admin', 'admin']); }
    public function isAccountsStaff(): bool { return $this->role === 'accounts_staff'; }
    public function isHrStaff(): bool     { return $this->role === 'hr_staff'; }

    public function canManageUsers(): bool       { return $this->isSuperAdmin(); }
    public function canProcessPayroll(): bool    { return $this->isAdmin(); }
    public function canViewReports(): bool       { return in_array($this->role, ['super_admin', 'admin', 'accounts_staff']); }
    public function canManageEmployees(): bool   { return $this->isAdmin() || $this->isHrStaff(); }
    public function canExportWPS(): bool         { return $this->isAdmin(); }
    public function canApprovePayroll(): bool    { return $this->isSuperAdmin(); }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'super_admin'    => 'Super Admin',
            'admin'          => 'Admin Staff',
            'accounts_staff' => 'Accounts Staff',
            'hr_staff'       => 'HR Staff',
            default          => ucfirst($this->role),
        };
    }
}
