<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => 'SO-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'status' => SalesOrder::STATUS_DRAFT,
            'notes' => null,
            'created_by' => User::factory()->admin(),
        ];
    }
}
