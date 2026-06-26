<?php

namespace Database\Seeders;

use App\Models\Fare;
use App\Models\Route;
use App\Models\SeatPlan;
use Illuminate\Database\Seeder;

class FareSeeder extends Seeder
{
    public function run(): void
    {
        $planA = SeatPlan::where('name', 'Standard 2+2 Single Deck')->first()->id;
        $planB = SeatPlan::where('name', 'Business 2+1 Single Deck')->first()->id;

        $routes = Route::all();

        foreach ($routes as $route) {
            // Standard 2+2 — Economy, AC
            Fare::create([
                'route_id' => $route->id,
                'seat_plan_id' => $planA,
                'coach_type' => Fare::COACH_TYPE_AC,
                'seat_type' => Fare::SEAT_TYPE_ECONOMY,
                'status' => Fare::STATUS_ACTIVE,
                'created_by' => 1,
            ]);

            // Standard 2+2 — Economy, Non-AC
            Fare::create([
                'route_id' => $route->id,
                'seat_plan_id' => $planA,
                'coach_type' => Fare::COACH_TYPE_NON_AC,
                'seat_type' => Fare::SEAT_TYPE_ECONOMY,
                'status' => Fare::STATUS_ACTIVE,
                'created_by' => 1,
            ]);

            // Business 2+1 — Business Class, AC
            Fare::create([
                'route_id' => $route->id,
                'seat_plan_id' => $planB,
                'coach_type' => Fare::COACH_TYPE_AC,
                'seat_type' => Fare::SEAT_TYPE_BUSINESS,
                'status' => Fare::STATUS_ACTIVE,
                'created_by' => 1,
            ]);

            // Business 2+1 — Business Class, Non-AC
            Fare::create([
                'route_id' => $route->id,
                'seat_plan_id' => $planB,
                'coach_type' => Fare::COACH_TYPE_NON_AC,
                'seat_type' => Fare::SEAT_TYPE_BUSINESS,
                'status' => Fare::STATUS_ACTIVE,
                'created_by' => 1,
            ]);
        }
    }
}
