<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Branch',
            'location' => fake()->streetAddress().', '.fake()->city(),
            'manager' => fake()->name(),
            'phone' => fake()->numerify('(02) 8###-####'),
            'email' => fake()->unique()->companyEmail(),
        ];
    }
}
