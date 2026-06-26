<?php

namespace Database\Seeders;

use App\Models\Bus;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $buses = [
            [
                'registration_number' => 'Dhaka Metro-GA-11-1234',
                'manufacturer_company' => 'Hino',
                'model_year' => 2020,
                'chasis_no' => 'HN-CH-2020-001',
                'engine_number' => 'HN-EN-2020-001',
                'color' => 'White',
                'country_of_origin' => 'Japan',
            ],
            [
                'registration_number' => 'Dhaka Metro-GA-11-5678',
                'manufacturer_company' => 'Hino',
                'model_year' => 2021,
                'chasis_no' => 'HN-CH-2021-002',
                'engine_number' => 'HN-EN-2021-002',
                'color' => 'Red',
                'country_of_origin' => 'Japan',
            ],
            [
                'registration_number' => 'Dhaka Metro-CH-12-2345',
                'manufacturer_company' => 'Volvo',
                'model_year' => 2019,
                'chasis_no' => 'VL-CH-2019-003',
                'engine_number' => 'VL-EN-2019-003',
                'color' => 'Blue',
                'country_of_origin' => 'Sweden',
            ],
            [
                'registration_number' => 'Chittagong Metro-BA-13-4567',
                'manufacturer_company' => 'Tata',
                'model_year' => 2022,
                'chasis_no' => 'TA-CH-2022-004',
                'engine_number' => 'TA-EN-2022-004',
                'color' => 'Silver',
                'country_of_origin' => 'India',
            ],
            [
                'registration_number' => 'Sylhet Metro-SA-14-6789',
                'manufacturer_company' => 'Ashok Leyland',
                'model_year' => 2021,
                'chasis_no' => 'AL-CH-2021-005',
                'engine_number' => 'AL-EN-2021-005',
                'color' => 'Green',
                'country_of_origin' => 'India',
            ],
        ];

        foreach ($buses as $bus) {
            Bus::create(array_merge($bus, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }
}
