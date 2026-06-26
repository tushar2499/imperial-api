<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = ['Driver', 'Supervisor', 'Helper', 'Mechanic', 'Counter Staff', 'Manager'];

        foreach ($designations as $name) {
            Designation::create([
                'name' => $name,
                'status' => 1,
                'created_by' => 1,
            ]);
        }
    }
}
