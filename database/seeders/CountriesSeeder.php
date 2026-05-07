<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // Common Middle East / GCC
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE'],
            ['name' => 'Saudi Arabia', 'iso_code' => 'SA'],
            ['name' => 'Qatar', 'iso_code' => 'QA'],
            ['name' => 'Kuwait', 'iso_code' => 'KW'],
            ['name' => 'Bahrain', 'iso_code' => 'BH'],
            ['name' => 'Oman', 'iso_code' => 'OM'],
            ['name' => 'Jordan', 'iso_code' => 'JO'],
            ['name' => 'Lebanon', 'iso_code' => 'LB'],
            ['name' => 'Iraq', 'iso_code' => 'IQ'],
            ['name' => 'Yemen', 'iso_code' => 'YE'],
            ['name' => 'Palestine', 'iso_code' => 'PS'],
            ['name' => 'Egypt', 'iso_code' => 'EG'],
            ['name' => 'Sudan', 'iso_code' => 'SD'],
            ['name' => 'Morocco', 'iso_code' => 'MA'],
            ['name' => 'Algeria', 'iso_code' => 'DZ'],
            ['name' => 'Tunisia', 'iso_code' => 'TN'],
            ['name' => 'Libya', 'iso_code' => 'LY'],
            ['name' => 'Nigeria', 'iso_code' => 'NG'],
            ['name' => 'Ghana', 'iso_code' => 'GH'],
            ['name' => 'Kenya', 'iso_code' => 'KE'],
            ['name' => 'Ethiopia', 'iso_code' => 'ET'],
            ['name' => 'Uganda', 'iso_code' => 'UG'],
            ['name' => 'Tanzania', 'iso_code' => 'TZ'],
            ['name' => 'South Africa', 'iso_code' => 'ZA'],

            // South Asia
            ['name' => 'India', 'iso_code' => 'IN'],
            ['name' => 'Pakistan', 'iso_code' => 'PK'],
            ['name' => 'Bangladesh', 'iso_code' => 'BD'],
            ['name' => 'Sri Lanka', 'iso_code' => 'LK'],

            // Southeast Asia
            ['name' => 'Philippines', 'iso_code' => 'PH'],
            ['name' => 'Indonesia', 'iso_code' => 'ID'],
            ['name' => 'Malaysia', 'iso_code' => 'MY'],
            ['name' => 'Thailand', 'iso_code' => 'TH'],
            ['name' => 'Vietnam', 'iso_code' => 'VN'],

            // Americas/Europe (nice-to-have)
            ['name' => 'United Kingdom', 'iso_code' => 'GB'],
            ['name' => 'England', 'iso_code' => null],
            ['name' => 'Scotland', 'iso_code' => null],
            ['name' => 'United States', 'iso_code' => 'US'],
            ['name' => 'Canada', 'iso_code' => 'CA'],
            ['name' => 'Ireland', 'iso_code' => 'IE'],
            ['name' => 'France', 'iso_code' => 'FR'],
            ['name' => 'Germany', 'iso_code' => 'DE'],

            // Africa (more common)
            ['name' => 'Zimbabwe', 'iso_code' => 'ZW'],
            ['name' => 'Zambia', 'iso_code' => 'ZM'],
            ['name' => 'Gambia', 'iso_code' => 'GM'],
            ['name' => 'Cameroon', 'iso_code' => 'CM'],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['name' => $country['name']],
                ['iso_code' => $country['iso_code']]
            );
        }
    }
}

