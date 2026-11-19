<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'barcode' => fake()->unique()->ean13(),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['Elektronik', 'Sparepart', 'Aksesoris', 'Peralatan']),
            'uom' => fake()->randomElement(['pcs', 'box', 'pack']),
            'rotation' => fake()->randomElement([Product::ROTATION_FIFO, Product::ROTATION_FEFO]),
            'min_stock' => fake()->numberBetween(5, 20),
            'max_stock' => fake()->numberBetween(100, 500),
            'unit_cost' => fake()->randomFloat(2, 1000, 500000),
            'weight_kg' => fake()->randomFloat(2, 0.1, 10),
            'is_active' => true,
        ];
    }
}
