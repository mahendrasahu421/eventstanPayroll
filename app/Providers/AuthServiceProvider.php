<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            // Admin access for super_admin and admin only.
            // IMPORTANT: some requests may pass a null user; treat as unauthorized.
            if (! $user) {
                return false;
            }

            // Prefer the model helper if available.
            if (method_exists($user, 'isAdmin')) {
                return (bool) $user->isAdmin();
            }

            // Fallback: normalize role values that might be stored with different casing/format.
            $role = null;
            if (isset($user->role)) {
                $role = (string) $user->role;
            }

            if ($role === '') {
                return false;
            }

            $normalized = strtolower(trim($role));
            $normalized = str_replace([' ', '-'], '_', $normalized);

            return in_array($normalized, ['super_admin', 'admin'], true);
        });
    }
}





