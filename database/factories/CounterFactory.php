<?php

namespace Database\Factories;

use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Counter>
 */
class CounterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'                   => fake()->randomElement(['1', '2', '3']),
            'address'                => fake()->address(),
            'land_mark'              => fake()->secondaryAddress(),
            'location_url'           => fake()->url(),
            'phone'                  => fake()->phoneNumber(),
            'mobile'                 => fake()->phoneNumber(),
            'email'                  => fake()->unique()->safeEmail(),
            'primary_contact_no'     => fake()->phoneNumber(),
            'country'                => fake()->country(),
            'district_id'            => District::factory(),
            'booking_allowed_status' => fake()->randomElement(['1', '2', '3']),
            'booking_allowed_class'  => fake()->randomElement(['1', '2', '3', '4']),
            'no_of_boarding_allowed' => fake()->numberBetween(1, 10),
            'sms_status'             => fake()->randomElement(['1', '2']),
            'status'                 => '1',
            'created_by'             => 1,
            'updated_by'             => 1,
        ];
    }
}
