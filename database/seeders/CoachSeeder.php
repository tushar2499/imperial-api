<?php

namespace Database\Seeders;

use App\Models\Coach;
use App\Models\SeatPlan;
use Illuminate\Database\Seeder;

class CoachSeeder extends Seeder
{
    public function run(): void
    {
        $planA = SeatPlan::where('name', 'Standard 2+2 Single Deck')->first()->id;
        $planB = SeatPlan::where('name', 'Business 2+1 Single Deck')->first()->id;

        $coaches = [
            ['coach_no' => 'IMP-001', 'seat_plan_id' => $planA, 'coach_type' => 2], // Single Deck
            ['coach_no' => 'IMP-002', 'seat_plan_id' => $planA, 'coach_type' => 2],
            ['coach_no' => 'IMP-003', 'seat_plan_id' => $planA, 'coach_type' => 2],
            ['coach_no' => 'IMP-004', 'seat_plan_id' => $planB, 'coach_type' => 2],
            ['coach_no' => 'IMP-005', 'seat_plan_id' => $planB, 'coach_type' => 2],
        ];

        foreach ($coaches as $coach) {
            Coach::create(array_merge($coach, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }
}
