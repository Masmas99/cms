<?php

namespace Database\Factories;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryStock>
 */
class InventoryStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $receivedAt = now()->subDays(fake()->numberBetween(1, 60));

        return [
            'location_id' => Location::factory(),
            'product_id' => Product::factory(),
            'batch_number' => 'BATCH-'.fake()->unique()->numerify('#####'),
            'expired_date' => null,
            'received_at' => $receivedAt,
            'qty_on_hand' => fake()->numberBetween(10, 100),
            'qty_reserved' => 0,
        ];
    }

    public function withExpiry(int $days = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'expired_date' => now()->addDays($days)->toDateString(),
        ]);
    }
}
