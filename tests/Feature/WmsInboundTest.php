<?php

namespace Tests\Feature;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiptItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WmsInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_receiving_stages_goods_and_put_away_moves_them_into_the_bin(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create(['code' => 'WH-TST']);
        $staging = Location::factory()->staging()->create([
            'warehouse_id' => $warehouse->id,
            'full_code' => 'WH-TST-STG-STG-STG-IN',
        ]);
        $bin = Location::factory()->create([
            'warehouse_id' => $warehouse->id,
            'full_code' => 'WH-TST-A-01-01-01',
        ]);
        $product = Product::factory()->create(['sku' => 'INB-001']);
        $supplier = Supplier::factory()->create();

        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'created_by' => $admin->id,
            'status' => PurchaseOrder::STATUS_SCHEDULED,
        ]);

        $item = $order->items()->create([
            'product_id' => $product->id,
            'qty_expected' => 10,
            'qty_received' => 0,
            'qty_rejected' => 0,
        ]);

        $this->actingAs($admin)
            ->post(wms_route('purchase-orders.receive.store', $order), [
                'notes' => 'Kedatangan pagi.',
                'items' => [
                    $item->id => [
                        'qty_good' => 8,
                        'qty_rejected' => 2,
                        'batch_number' => 'BATCH-INB-1',
                    ],
                ],
            ])
            ->assertRedirect(wms_route('purchase-orders.put-away', $order))
            ->assertSessionHas('success');

        $this->assertSame(PurchaseOrder::STATUS_RECEIVING, $order->fresh()->status);

        $this->assertDatabaseHas('inventory_stocks', [
            'location_id' => $staging->id,
            'product_id' => $product->id,
            'batch_number' => 'BATCH-INB-1',
            'qty_on_hand' => 8,
        ]);

        $this->assertDatabaseHas('receipt_items', [
            'purchase_order_item_id' => $item->id,
            'qty_good' => 8,
            'qty_rejected' => 2,
            'status' => ReceiptItem::STATUS_STAGED,
        ]);

        $receiptItem = ReceiptItem::query()
            ->where('purchase_order_item_id', $item->id)
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(wms_route('purchase-orders.put-away.store', $order), [
                'items' => [
                    $receiptItem->id => ['location_id' => $bin->id],
                ],
            ])
            ->assertRedirect(wms_route('purchase-orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stocks', [
            'location_id' => $bin->id,
            'product_id' => $product->id,
            'batch_number' => 'BATCH-INB-1',
            'qty_on_hand' => 8,
        ]);

        $this->assertSame(0.0, (float) InventoryStock::query()
            ->where('location_id', $staging->id)
            ->where('product_id', $product->id)
            ->value('qty_on_hand'));

        $this->assertSame(ReceiptItem::STATUS_PUT_AWAY, $receiptItem->fresh()->status);
        $this->assertSame($bin->id, $receiptItem->fresh()->put_away_location_id);
        $this->assertSame(PurchaseOrder::STATUS_COMPLETED, $order->fresh()->status);

        $this->assertTrue(StockMovement::query()
            ->where('type', StockMovement::TYPE_TRANSFER_IN)
            ->where('quantity', 8)
            ->exists());
    }

    public function test_receiving_cannot_exceed_the_outstanding_quantity(): void
    {
        $admin = User::factory()->admin()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $order = PurchaseOrder::factory()->create([
            'warehouse_id' => $warehouse->id,
            'created_by' => $admin->id,
        ]);

        $item = $order->items()->create([
            'product_id' => $product->id,
            'qty_expected' => 5,
            'qty_received' => 0,
            'qty_rejected' => 0,
        ]);

        $this->actingAs($admin)
            ->from(wms_route('purchase-orders.receive', $order))
            ->post(wms_route('purchase-orders.receive.store', $order), [
                'items' => [
                    $item->id => ['qty_good' => 6],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('receipt_items', 0);
        $this->assertSame(0.0, (float) $item->fresh()->qty_received);
    }
}
