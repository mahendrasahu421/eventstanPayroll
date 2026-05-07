<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::firstOrCreate(
            ['company_name' => 'My Company LLC'],
            [
                'company_email'          => 'hr@mycompany.com',
                'company_phone'          => '+971 4 000 0000',
                'company_address'        => 'Dubai, UAE',
                'currency'               => 'AED',
                'currency_symbol'        => 'د.إ',
                'working_days_per_month' => 26,
            ]
        );
    }
}

