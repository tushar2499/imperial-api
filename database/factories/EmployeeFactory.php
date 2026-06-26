<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'contact_no' => fake()->numerify('017########'),
            'email' => fake()->optional()->safeEmail(),
            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'joining_date' => fake()->optional()->date('Y-m-d'),
            'job_type' => fake()->optional()->randomElement(['Full-time', 'Part-time', 'Contract']),
            'religion' => fake()->optional()->randomElement(['Islam', 'Hindu', 'Christian', 'Buddhist']),
            'blood_group' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'marital_status' => fake()->optional()->randomElement(['Single', 'Married', 'Divorced']),
            'status' => 1,
            'created_by' => 1,
        ];
    }
}
