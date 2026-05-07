<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

use Database\Seeders\CountriesSeeder;
use Database\Seeders\CompanySettingsSeeder;



class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed master data (lookup tables)
        $this->call(CountriesSeeder::class);

        // ── Company Settings (Master Data) ─────────────────────────────────────────
        $this->call(CompanySettingsSeeder::class);



        // ── Super Admin ───────────────────────────────────────────────────────

        User::updateOrCreate(
            ['email' => 'admin@payroll.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'role'      => 'super_admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'hr@payroll.com'],
            [
                'name'      => 'HR Manager',
                'password'  => Hash::make('password'),
                'role'      => 'hr_staff',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'accounts@payroll.com'],
            [
                'name'      => 'Accounts Staff',
                'password'  => Hash::make('password'),
                'role'      => 'accounts_staff',
                'is_active' => true,
            ]
        );


        // ── Departments & Designations ────────────────────────────────────────
        $departments = [
            'Engineering'    => ['Senior Engineer', 'Junior Engineer', 'Team Lead'],
            'Human Resources'=> ['HR Manager', 'HR Coordinator'],
            'Finance'        => ['Finance Manager', 'Accountant', 'Junior Accountant'],
            'Operations'     => ['Operations Manager', 'Supervisor', 'Driver'],
            'Sales'          => ['Sales Manager', 'Sales Executive'],
        ];

        foreach ($departments as $deptName => $roles) {
            $dept = Department::create(['name' => $deptName, 'is_active' => true]);
            foreach ($roles as $role) {
                Designation::create(['department_id' => $dept->id, 'name' => $role, 'is_active' => true]);
            }
        }
    }
}
