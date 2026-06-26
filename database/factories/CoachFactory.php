<?php

namespace Database\Factories;

use App\Models\SeatPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coach>
 */
class CoachFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coach_no' => strtoupper(fake()->unique()->bothify('??-###')),
            'seat_plan_id' => SeatPlan::factory(),
            'coach_type' => fake()->randomElement([1, 2]),
            'status' => 1,
        ];
    }
}
