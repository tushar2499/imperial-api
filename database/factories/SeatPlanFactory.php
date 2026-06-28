<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeatPlan>
 */
class SeatPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' Plan',
            'floor' => fake()->randomElement([1, 2]),
            'rows' => fake()->numberBetween(5, 15),
            'cols' => fake()->numberBetween(2, 6),
            'layout_type' => fake()->randomElement(['2+2', '2+1', null]),
            'status' => 1,
        ];
    }
}
