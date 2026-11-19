<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $zone = 'Z'.fake()->numberBetween(1, 4);
        $aisle = 'A'.fake()->numberBetween(1, 6);
        $rack = 'R'.fake()->numberBetween(1, 8);
        $bin = 'B'.fake()->unique()->numerify('###');

        return [
            'warehouse_id' => Warehouse::factory(),
            'zone' => $zone,
            'aisle' => $aisle,
            'rack' => $rack,
            'bin_code' => $bin,
            'full_code' => strtoupper('WH-'.fake()->unique()->numerify('###').'-'.$zone.'-'.$aisle.'-'.$rack.'-'.$bin),
            'capacity_m3' => fake()->randomFloat(2, 0.5, 5),
            'capacity_kg' => fake()->randomFloat(2, 50, 500),
            'is_staging' => false,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the bin is a receiving staging area.
     */
    public function staging(): static
    {
        return $this->state(fn (array $attributes): array => [
            'zone' => 'STG',
            'aisle' => 'STG',
            'rack' => 'STG',
            'bin_code' => 'IN',
            'is_staging' => true,
        ]);
    }
}
