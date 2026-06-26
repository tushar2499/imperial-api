<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = ['Morning', 'Afternoon', 'Evening', 'Night'];

        foreach ($schedules as $name) {
            Schedule::create([
                'name' => $name,
                'status' => 1,
                'created_by' => 1,
            ]);
        }
    }
}
