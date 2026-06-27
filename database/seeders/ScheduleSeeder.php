<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            '06:00 AM',
            '07:30 AM',
            '09:00 AM',
            '10:30 AM',
            '12:00 PM',
            '01:30 PM',
            '03:00 PM',
            '04:30 PM',
            '06:00 PM',
            '07:30 PM',
            '09:00 PM',
            '10:30 PM',
            '11:59 PM',
        ];

        foreach ($schedules as $name) {
            Schedule::firstOrCreate(
                ['name' => $name],
                ['status' => 1, 'created_by' => 1]
            );
        }
    }
}
