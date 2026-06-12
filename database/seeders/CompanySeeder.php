<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'company_code' => 'COMP000000001',
                'company_name' => 'Al Noor Holdings LLC',
                'company_email' => 'info@alnoorholdings.com',
                'company_phone' => '+971 4 111 2222',
                'company_address' => 'Dubai, UAE',
                'currency' => 'AED',
                'currency_symbol' => 'د.إ',
                'working_days_per_month' => 30,
                'is_active' => true,
            ],
            [
                'company_code' => 'COMP000000002',
                'company_name' => 'Blue Sky Trading FZ-LLC',
                'company_email' => 'contact@blueskytrading.com',
                'company_phone' => '+971 4 333 4444',
                'company_address' => 'Abu Dhabi, UAE',
                'currency' => 'AED',
                'currency_symbol' => 'د.إ',
                'working_days_per_month' => 30,
                'is_active' => true,
            ],
            [
                'company_code' => 'COMP000000003',
                'company_name' => 'Evergreen Services LLC',
                'company_email' => 'hr@evergreenservices.com',
                'company_phone' => '+971 2 555 6666',
                'company_address' => 'Sharjah, UAE',
                'currency' => 'AED',
                'currency_symbol' => 'د.إ',
                'working_days_per_month' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['company_code' => $company['company_code']],
                [
                    'company_name' => $company['company_name'],
                    'company_email' => $company['company_email'],
                    'company_phone' => $company['company_phone'],
                    'company_address' => $company['company_address'],
                    'logo' => null,
                    'currency' => $company['currency'],
                    'currency_symbol' => $company['currency_symbol'],
                    'working_days_per_month' => $company['working_days_per_month'],
                    'is_active' => $company['is_active'],
                ]
            );
        }
    }
}

