<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_no' => 'PO-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory()->admin(),
            'expected_date' => now()->addDays(3)->toDateString(),
            'status' => PurchaseOrder::STATUS_SCHEDULED,
            'notes' => null,
        ];
    }
}
