<?php

namespace Database\Seeders;

use App\Models\Seat;
use App\Models\SeatPlan;
use App\Models\SeatPlanFloor;
use Illuminate\Database\Seeder;

class SeatPlanSeeder extends Seeder
{
    public function run(): void
    {
        $this->createStandard2x2Plan();
        $this->createBusiness2x1Plan();
    }

    /**
     * Plan A: Standard Single Deck 2+2 (40 seats, Economy)
     */
    private function createStandard2x2Plan(): void
    {
        $plan = SeatPlan::create([
            'name' => 'Standard 2+2 Single Deck',
            'floor' => 1,
            'layout_type' => '2+2',
            'created_by' => 1,
        ]);

        $floor = SeatPlanFloor::create([
            'seat_plan_id' => $plan->id,
            'name' => 'Lower Deck',
            'layout_type' => '2+2',
            'rows' => 10,
            'cols' => 4,
            'step' => 4,
            'is_extra_seat' => false,
            'created_by' => 1,
        ]);

        $rowLabels = range('A', 'J'); // 10 rows

        foreach ($rowLabels as $rowIndex => $rowLabel) {
            for ($col = 1; $col <= 4; $col++) {
                Seat::create([
                    'seat_plan_id' => $plan->id,
                    'seat_plan_floor_id' => $floor->id,
                    'seat_number' => $rowLabel.$col,
                    'row_position' => $rowIndex + 1,
                    'col_position' => $col,
                    'seat_type' => 'Economy',
                    'status' => 1,
                    'created_by' => 1,
                ]);
            }
        }
    }

    /**
     * Plan B: Business Single Deck 2+1 (30 seats, Business Class)
     * Layout: col 1-2 left side, col 3 right side (no aisle seat in data)
     */
    private function createBusiness2x1Plan(): void
    {
        $plan = SeatPlan::create([
            'name' => 'Business 2+1 Single Deck',
            'floor' => 1,
            'layout_type' => '2+1',
            'created_by' => 1,
        ]);

        $floor = SeatPlanFloor::create([
            'seat_plan_id' => $plan->id,
            'name' => 'Lower Deck',
            'layout_type' => '2+1',
            'rows' => 10,
            'cols' => 3,
            'step' => 3,
            'is_extra_seat' => false,
            'created_by' => 1,
        ]);

        $rowLabels = range('A', 'J'); // 10 rows

        foreach ($rowLabels as $rowIndex => $rowLabel) {
            for ($col = 1; $col <= 3; $col++) {
                Seat::create([
                    'seat_plan_id' => $plan->id,
                    'seat_plan_floor_id' => $floor->id,
                    'seat_number' => $rowLabel.$col,
                    'row_position' => $rowIndex + 1,
                    'col_position' => $col,
                    'seat_type' => 'Business Class',
                    'status' => 1,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
