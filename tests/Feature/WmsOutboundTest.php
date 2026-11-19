<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\StockAllocation;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WmsOutboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_fifo_reservation_takes_the_oldest_received_lot_first(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create(['rotation' => Product::ROTATION_FIFO]);

        $oldLot = InventoryStock::factory()->create([
            'location_id' => Location::factory()->create(['warehouse_id' => $warehouse->id]),
            'product_id' => $product->id,
            'batch_number' => 'OLD',
            'received_at' => now()->subDays(20),
            'expired_date' => now()->addDays(120)->toDateString(),
            'qty_on_hand' => 10,
        ]);

        $newLot = InventoryStock::factory()->create([
            'location_id' => Location::factory()->create(['warehouse_id' => $warehouse->id]),
            'product_id' => $product->id,
            'batch_number' => 'NEW',
            'received_at' => now()->subDays(2),
            'expired_date' => now()->addDays(10)->toDateString(),
            'qty_on_hand' => 10,
        ]);

        $order = $this->orderFor($admin, $product, 6);

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.reserve', $order))
            ->assertRedirect(wms_route('sales-orders.show', $order));

        $allocation = StockAllocation::query()->firstOrFail();

        $this->assertSame($oldLot->id, $allocation->inventory_stock_id);
        $this->assertSame(6.0, $allocation->quantity);
        $this->assertSame(6.0, (float) $oldLot->fresh()->qty_reserved);
        $this->assertSame(0.0, (float) $newLot->fresh()->qty_reserved);
        $this->assertSame(SalesOrder::STATUS_RESERVED, $order->fresh()->status);
    }

    public function test_fefo_reservation_takes_the_soonest_expiry_lot_first(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create(['rotation' => Product::ROTATION_FEFO]);

        $longLife = InventoryStock::factory()->create([
            'location_id' => Location::factory()->create(['warehouse_id' => $warehouse->id]),
            'product_id' => $product->id,
            'batch_number' => 'LONG',
            'received_at' => now()->subDays(30),
            'expired_date' => now()->addDays(120)->toDateString(),
            'qty_on_hand' => 10,
        ]);

        $shortLife = InventoryStock::factory()->create([
            'location_id' => Location::factory()->create(['warehouse_id' => $warehouse->id]),
            'product_id' => $product->id,
            'batch_number' => 'SHORT',
            'received_at' => now()->subDays(1),
            'expired_date' => now()->addDays(5)->toDateString(),
            'qty_on_hand' => 10,
        ]);

        $order = $this->orderFor($admin, $product, 3);

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.reserve', $order))
            ->assertRedirect(wms_route('sales-orders.show', $order));

        $allocation = StockAllocation::query()->firstOrFail();

        $this->assertSame($shortLife->id, $allocation->inventory_stock_id);
        $this->assertSame(0.0, (float) $longLife->fresh()->qty_reserved);
    }

    public function test_pick_pack_and_ship_complete_the_outbound_flow(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $bin = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
            'full_code' => 'WH-OUT-A-01-01-01',
        ]);
        $product = Product::factory()->create(['rotation' => Product::ROTATION_FIFO]);

        $stock = InventoryStock::factory()->create([
            'location_id' => $bin->id,
            'product_id' => $product->id,
            'batch_number' => 'OUT-1',
            'qty_on_hand' => 10,
            'qty_reserved' => 0,
        ]);

        $courier = Courier::factory()->create();
        $order = $this->orderFor($admin, $product, 4);

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.reserve', $order))
            ->assertRedirect(wms_route('sales-orders.show', $order));

        $allocation = StockAllocation::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.pick', $order), [
                'code' => $bin->full_code,
            ])
            ->assertRedirect(wms_route('sales-orders.packing', $order));

        $this->assertNotNull($allocation->fresh()->picked_at);
        $this->assertSame(6.0, (float) $stock->fresh()->qty_on_hand);
        $this->assertSame(0.0, (float) $stock->fresh()->qty_reserved);
        $this->assertSame(SalesOrder::STATUS_PICKING, $order->fresh()->status);

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_OUTBOUND,
            'reference_no' => $order->reference_no,
            'quantity' => -4,
        ]);

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.pack', $order), [
                'allocation' => $allocation->id,
            ])
            ->assertRedirect(wms_route('sales-orders.packing', $order));

        $this->assertNotNull($allocation->fresh()->packed_at);
        $this->assertSame(SalesOrder::STATUS_PACKED, $order->fresh()->status);

        $this->assertDatabaseHas('shipments', [
            'sales_order_id' => $order->id,
            'packed_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(wms_route('sales-orders.ship', $order), [
                'courier_id' => $courier->id,
                'tracking_number' => 'RESI-123456',
            ])
            ->assertRedirect(wms_route('sales-orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame(SalesOrder::STATUS_SHIPPED, $order->fresh()->status);

        $this->assertDatabaseHas('shipments', [
            'sales_order_id' => $order->id,
            'courier_id' => $courier->id,
            'tracking_number' => 'RESI-123456',
        ]);
    }

    public function test_reservation_fails_when_stock_is_insufficient(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        InventoryStock::factory()->create([
            'location_id' => Location::factory()->create(['warehouse_id' => $warehouse->id]),
            'product_id' => $product->id,
            'qty_on_hand' => 2,
        ]);

        $order = $this->orderFor($admin, $product, 5);

        $this->actingAs($admin)
            ->from(wms_route('sales-orders.show', $order))
            ->post(wms_route('sales-orders.reserve', $order))
            ->assertSessionHas('error');

        $this->assertSame(SalesOrder::STATUS_DRAFT, $order->fresh()->status);
        $this->assertDatabaseCount('stock_allocations', 0);
    }

    private function orderFor(User $admin, Product $product, float $quantity): SalesOrder
    {
        $order = SalesOrder::factory()->create([
            'created_by' => $admin->id,
            'status' => SalesOrder::STATUS_DRAFT,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty_ordered' => $quantity,
            'qty_allocated' => 0,
            'qty_picked' => 0,
        ]);

        return $order;
    }
}
