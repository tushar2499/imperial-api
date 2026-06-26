<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            ['name' => 'Dhaka',       'code' => 'DHK', 'status' => 1],
            ['name' => 'Chittagong',  'code' => 'CTG', 'status' => 1],
            ['name' => 'Sylhet',      'code' => 'SYL', 'status' => 1],
            ['name' => 'Rajshahi',    'code' => 'RJS', 'status' => 1],
            ['name' => 'Khulna',      'code' => 'KHU', 'status' => 1],
            ['name' => 'Barisal',     'code' => 'BRS', 'status' => 1],
            ['name' => 'Mymensingh',  'code' => 'MYM', 'status' => 1],
            ['name' => 'Rangpur',     'code' => 'RGP', 'status' => 1],
            ['name' => 'Comilla',     'code' => 'COM', 'status' => 1],
            ['name' => 'Narsingdi',   'code' => 'NSD', 'status' => 1],
        ];

        foreach ($districts as $district) {
            District::create($district);
        }
    }
}
