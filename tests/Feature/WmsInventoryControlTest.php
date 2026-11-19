<?php

namespace Tests\Feature;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WmsInventoryControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_transfer_moves_quantity_between_bins(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $from = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $to = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();

        $stock = InventoryStock::factory()->create([
            'location_id' => $from->id,
            'product_id' => $product->id,
            'batch_number' => 'TRF-1',
            'qty_on_hand' => 10,
            'qty_reserved' => 0,
        ]);

        $this->actingAs($user)
            ->post(wms_route('transfers.store'), [
                'inventory_stock_id' => $stock->id,
                'to_location_id' => $to->id,
                'quantity' => 4,
                'notes' => 'Pemindahan bin.',
            ])
            ->assertRedirect(wms_route('transfers.index'))
            ->assertSessionHas('success');

        $this->assertSame(6.0, (float) $stock->fresh()->qty_on_hand);

        $this->assertDatabaseHas('inventory_stocks', [
            'location_id' => $to->id,
            'product_id' => $product->id,
            'batch_number' => 'TRF-1',
            'qty_on_hand' => 4,
        ]);

        $this->assertDatabaseHas('stock_transfers', [
            'product_id' => $product->id,
            'from_location_id' => $from->id,
            'to_location_id' => $to->id,
            'quantity' => 4,
            'status' => StockTransfer::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_TRANSFER_OUT,
            'quantity' => -4,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_TRANSFER_IN,
            'quantity' => 4,
        ]);
    }

    public function test_transfer_cannot_exceed_available_quantity(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $from = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $to = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();

        $stock = InventoryStock::factory()->create([
            'location_id' => $from->id,
            'product_id' => $product->id,
            'qty_on_hand' => 5,
            'qty_reserved' => 3,
        ]);

        $this->actingAs($user)
            ->from(wms_route('transfers.create'))
            ->post(wms_route('transfers.store'), [
                'inventory_stock_id' => $stock->id,
                'to_location_id' => $to->id,
                'quantity' => 3,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(5.0, (float) $stock->fresh()->qty_on_hand);
        $this->assertDatabaseCount('stock_transfers', 0);
    }

    public function test_blind_count_creates_a_pending_adjustment_that_only_an_admin_can_approve(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $bin = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();

        $stock = InventoryStock::factory()->create([
            'location_id' => $bin->id,
            'product_id' => $product->id,
            'batch_number' => 'OPN-1',
            'qty_on_hand' => 10,
            'qty_reserved' => 0,
        ]);

        $this->actingAs($admin)
            ->post(wms_route('opnames.store'), [
                'warehouse_id' => $warehouse->id,
                'scheduled_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $opname = StockOpname::query()->firstOrFail();
        $opnameItem = $opname->items()->firstOrFail();

        $this->assertSame(10.0, $opnameItem->system_qty);
        $this->assertSame($bin->id, $opnameItem->location_id);

        $this->actingAs($staff)
            ->post(wms_route('opnames.count', $opname), [
                'items' => [$opnameItem->id => 7],
            ])
            ->assertRedirect(wms_route('opnames.show', $opname));

        $opnameItem->refresh();

        $this->assertSame(7.0, $opnameItem->counted_qty);
        $this->assertSame(-3.0, $opnameItem->difference);
        $this->assertSame($staff->id, $opnameItem->counted_by);
        $this->assertTrue($opname->fresh()->isFullyCounted());

        $adjustment = StockAdjustment::query()->firstOrFail();

        $this->assertSame(StockAdjustment::STATUS_PENDING, $adjustment->status);
        $this->assertSame(-3.0, (float) $adjustment->quantity_difference);
        $this->assertSame($staff->id, $adjustment->requested_by);

        $this->actingAs($staff)
            ->post(wms_route('adjustments.approve', $adjustment))
            ->assertForbidden();

        $this->assertSame(StockAdjustment::STATUS_PENDING, $adjustment->fresh()->status);
        $this->assertSame(10.0, (float) $stock->fresh()->qty_on_hand);

        $this->actingAs($admin)
            ->post(wms_route('adjustments.approve', $adjustment), [
                'decision_note' => 'Sudah dicek ulang.',
            ])
            ->assertSessionHas('success');

        $this->assertSame(StockAdjustment::STATUS_APPROVED, $adjustment->fresh()->status);
        $this->assertSame($admin->id, $adjustment->fresh()->approved_by);
        $this->assertSame(7.0, (float) $stock->fresh()->qty_on_hand);

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_ADJUSTMENT_OUT,
            'reference_no' => $adjustment->reference_no,
            'quantity' => -3,
        ]);
    }

    public function test_opname_cannot_be_completed_before_every_line_is_counted(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $bin = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();

        InventoryStock::factory()->create([
            'location_id' => $bin->id,
            'product_id' => $product->id,
            'qty_on_hand' => 5,
        ]);

        $this->actingAs($admin)->post(wms_route('opnames.store'), [
            'warehouse_id' => $warehouse->id,
            'scheduled_date' => now()->toDateString(),
        ]);

        $opname = StockOpname::query()->firstOrFail();

        $this->actingAs($admin)
            ->from(wms_route('opnames.show', $opname))
            ->post(wms_route('opnames.complete', $opname))
            ->assertSessionHas('error');

        $this->assertSame(StockOpname::STATUS_COUNTING, $opname->fresh()->status);
    }

    public function test_admin_disposition_restocks_a_returned_item(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $bin = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();

        $this->actingAs($staff)
            ->post(wms_route('returns.store'), [
                'customer_name' => 'PT Pelanggan',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 3,
                        'condition' => 'good',
                        'batch_number' => 'RMA-1',
                    ],
                ],
            ])
            ->assertRedirect();

        $return = ReturnOrder::query()->firstOrFail();
        $item = $return->items()->firstOrFail();

        $this->assertSame(ReturnOrder::STATUS_RECEIVED, $return->status);
        $this->assertSame(ReturnItem::DISPOSITION_PENDING, $item->disposition);

        $this->actingAs($staff)
            ->post(wms_route('returns.disposition', [$return, $item]), [
                'disposition' => 'restock',
                'location_id' => $bin->id,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(wms_route('returns.disposition', [$return, $item]), [
                'disposition' => 'restock',
                'location_id' => $bin->id,
            ])
            ->assertRedirect();

        $item->refresh();

        $this->assertSame(ReturnItem::DISPOSITION_RESTOCK, $item->disposition);
        $this->assertSame($bin->id, $item->location_id);
        $this->assertSame($admin->id, $item->decided_by);
        $this->assertSame(ReturnOrder::STATUS_COMPLETED, $return->fresh()->status);

        $this->assertDatabaseHas('inventory_stocks', [
            'location_id' => $bin->id,
            'product_id' => $product->id,
            'batch_number' => 'RMA-1',
            'qty_on_hand' => 3,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_RETURN,
            'reference_no' => $return->reference_no,
            'quantity' => 3,
        ]);
    }
}
