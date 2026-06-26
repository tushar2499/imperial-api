<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DistrictSeeder::class,
            CounterSeeder::class,
            RouteSeeder::class,
            StationSeeder::class,
            ScheduleSeeder::class,
            SeatPlanSeeder::class,
            BusSeeder::class,
            DesignationSeeder::class,
            EmployeeSeeder::class,
            CoachSeeder::class,
            FareSeeder::class,
            CoachConfigurationSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
