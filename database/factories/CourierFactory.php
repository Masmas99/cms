<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'CR-'.fake()->unique()->numerify('###'),
            'name' => fake()->company(),
            'tracking_url' => 'https://example.com/track/',
            'is_active' => true,
        ];
    }
}
