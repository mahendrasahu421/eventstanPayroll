<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdditionalAdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Adds an extra Admin and an extra Super Admin.
        // NOTE: passwords are defaulted to 'password' (same as existing DatabaseSeeder credentials).

        User::updateOrCreate(
            ['email' => 'admin2@payroll.com'],
            [
                'name'      => 'Admin Staff 2',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'superadmin2@payroll.com'],
            [
                'name'      => 'Super Admin 2',
                'password'  => Hash::make('password'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}

