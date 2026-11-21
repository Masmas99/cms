<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiptItem;
use App\Models\ReturnItem;
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DummyDataSeeder extends Seeder
{
    private const COUNT = 1000;

    /**
     * @var list<string>
     */
    private array $userIds = [];

    private Carbon $now;

    private string $adminId;

    /**
     * Seed 1.000 dummy rows for every WMS feature without creating duplicates.
     */
    public function run(): void
    {
        $this->now = now();
        $admin = $this->adminUser();
        $this->adminId = $admin->id;

        $this->userIds = User::query()
            ->orderBy('id')
            ->limit(100)
            ->pluck('id')
            ->all();

        if ($this->userIds === []) {
            $this->userIds = [$admin->id];
        }

        $this->seedMasterData();
        $this->seedPurchasing();
        $this->seedInventory();
        $this->seedSales();
        $this->seedStockControl();
        $this->seedReturns();
        $this->seedAuditLogs();

        $this->command?->info('Data dummy 1.000 baris per fitur selesai dibuat.');
    }

    private function adminUser(): User
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->first()
            ?? User::query()->first();

        if ($admin !== null) {
            return $admin;
        }

        return User::factory()->admin()->create([
            'name' => 'Dummy Administrator',
            'email' => 'dummy-admin@example.com',
        ]);
    }

    private function seedMasterData(): void
    {
        $this->seedWarehouses();
        $this->seedLocations();
        $this->seedProducts();
        $this->seedSuppliers();
        $this->seedCouriers();
    }

    private function seedWarehouses(): void
    {
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Makassar', 'Palembang', 'Denpasar', 'Yogyakarta', 'Balikpapan'];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $city = $cities[($index - 1) % count($cities)];

            $rows[] = [
                'id' => $this->id('warehouses', $index),
                'code' => 'DUM-WH-'.$this->pad($index),
                'name' => 'Gudang Dummy '.$this->pad($index),
                'address' => 'Jl. Dummy Raya No. '.$index.', '.$city,
                'city' => $city,
                'is_active' => $index % 20 !== 0,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('warehouses', $rows);
    }

    private function seedLocations(): void
    {
        $zones = ['A', 'B', 'C', 'D', 'E'];
        $aisles = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
        $racks = ['01', '02', '03', '04', '05', '06', '07', '08'];
        $bins = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $zone = $zones[($index - 1) % count($zones)];
            $aisle = $aisles[intdiv($index - 1, count($zones)) % count($aisles)];
            $rack = $racks[intdiv($index - 1, count($zones) * count($aisles)) % count($racks)];
            $bin = $bins[intdiv($index - 1, count($zones) * count($aisles) * count($racks)) % count($bins)];

            $rows[] = [
                'id' => $this->id('locations', $index),
                'warehouse_id' => $this->id('warehouses', $index),
                'zone' => $zone,
                'aisle' => $aisle,
                'rack' => $rack,
                'bin_code' => $bin,
                'full_code' => 'DUM-WH-'.$this->pad($index).'-'.$zone.'-'.$aisle.'-'.$rack.'-'.$bin,
                'capacity_m3' => 1 + ($index % 20) / 2,
                'capacity_kg' => 100 + ($index % 400),
                'is_staging' => false,
                'is_active' => true,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('locations', $rows);
    }

    private function seedProducts(): void
    {
        $categories = ['Jaringan', 'Perangkat', 'Aksesoris', 'Peralatan', 'Kabel', 'Konektor'];
        $uoms = ['pcs', 'unit', 'roll', 'pack', 'box'];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $minStock = 5 + ($index % 20);

            $rows[] = [
                'id' => $this->id('products', $index),
                'sku' => 'DUM-SKU-'.$this->pad($index),
                'barcode' => sprintf('8998%09d', $index),
                'name' => 'Produk Dummy '.$this->pad($index),
                'category' => $categories[($index - 1) % count($categories)],
                'uom' => $uoms[($index - 1) % count($uoms)],
                'rotation' => $index % 4 === 0 ? Product::ROTATION_FEFO : Product::ROTATION_FIFO,
                'min_stock' => $minStock,
                'max_stock' => $minStock * 10,
                'unit_cost' => 10000 + ($index * 137),
                'weight_kg' => 0.1 + (($index % 50) / 10),
                'is_active' => $index % 25 !== 0,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('products', $rows);
    }

    private function seedSuppliers(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('suppliers', $index),
                'code' => 'DUM-SUP-'.$this->pad($index),
                'name' => 'Supplier Dummy '.$this->pad($index),
                'contact_person' => 'Kontak Supplier '.$index,
                'phone' => '021-555'.$this->pad($index),
                'email' => 'supplier.dummy'.$index.'@example.com',
                'address' => 'Jl. Supplier Dummy No. '.$index,
                'is_active' => true,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('suppliers', $rows);
    }

    private function seedCouriers(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('couriers', $index),
                'code' => 'DUM-CRR-'.$this->pad($index),
                'name' => 'Kurir Dummy '.$this->pad($index),
                'tracking_url' => 'https://example.com/track/dummy-'.$index,
                'is_active' => true,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('couriers', $rows);
    }

    private function seedPurchasing(): void
    {
        $statuses = $this->purchaseOrderStatuses();
        $receivableIndexes = array_keys(array_filter(
            $statuses,
            fn (string $status): bool => in_array($status, [PurchaseOrder::STATUS_RECEIVING, PurchaseOrder::STATUS_COMPLETED], true),
        ));

        $this->seedPurchaseOrders($statuses);
        $this->seedPurchaseOrderItems($statuses);
        $this->seedReceipts($receivableIndexes);
        $this->seedReceiptItems($receivableIndexes);
    }

    /**
     * @return array<int, string>
     */
    private function purchaseOrderStatuses(): array
    {
        $statuses = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $statuses[$index] = match (true) {
                $index % 10 <= 3 => PurchaseOrder::STATUS_COMPLETED,
                $index % 10 <= 6 => PurchaseOrder::STATUS_RECEIVING,
                $index % 10 <= 8 => PurchaseOrder::STATUS_SCHEDULED,
                default => PurchaseOrder::STATUS_CANCELLED,
            };
        }

        return $statuses;
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function seedPurchaseOrders(array $statuses): void
    {
        $rows = [];

        foreach ($statuses as $index => $status) {
            $rows[] = [
                'id' => $this->id('purchase_orders', $index),
                'reference_no' => 'DUM-PO-'.$this->pad($index),
                'supplier_id' => $this->id('suppliers', $index),
                'warehouse_id' => $this->id('warehouses', $index),
                'created_by' => $this->userId($index),
                'expected_date' => $this->now->copy()->addDays(($index % 60) - 30)->toDateString(),
                'status' => $status,
                'notes' => 'Purchase order dummy '.$index,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('purchase_orders', $rows);
    }

    /**
     * @param  array<int, string>  $statuses
     */
    private function seedPurchaseOrderItems(array $statuses): void
    {
        $rows = [];

        foreach ($statuses as $index => $status) {
            $expected = 10 + ($index % 90);
            [$received, $rejected] = match ($status) {
                PurchaseOrder::STATUS_COMPLETED => [$expected, 0],
                PurchaseOrder::STATUS_RECEIVING => [intdiv($expected, 2), 0],
                default => [0, 0],
            };

            $rows[] = [
                'id' => $this->id('purchase_order_items', $index),
                'purchase_order_id' => $this->id('purchase_orders', $index),
                'product_id' => $this->id('products', $index),
                'qty_expected' => $expected,
                'qty_received' => $received,
                'qty_rejected' => $rejected,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('purchase_order_items', $rows);
    }

    /**
     * @param  list<int>  $receivableIndexes
     */
    private function seedReceipts(array $receivableIndexes): void
    {
        $rows = [];
        $receivableCount = count($receivableIndexes);

        for ($index = 1; $index <= self::COUNT; $index++) {
            $purchaseOrderIndex = $receivableIndexes[($index - 1) % $receivableCount];

            $rows[] = [
                'id' => $this->id('receipts', $index),
                'reference_no' => 'DUM-RCV-'.$this->pad($index),
                'purchase_order_id' => $this->id('purchase_orders', $purchaseOrderIndex),
                'received_by' => $this->userId($index),
                'received_at' => $this->timestampFor($index),
                'notes' => 'Penerimaan dummy '.$index,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('receipts', $rows);
    }

    /**
     * @param  list<int>  $receivableIndexes
     */
    private function seedReceiptItems(array $receivableIndexes): void
    {
        $rows = [];
        $receivableCount = count($receivableIndexes);

        for ($index = 1; $index <= self::COUNT; $index++) {
            $purchaseOrderIndex = $receivableIndexes[($index - 1) % $receivableCount];

            $rows[] = [
                'id' => $this->id('receipt_items', $index),
                'receipt_id' => $this->id('receipts', $index),
                'purchase_order_item_id' => $this->id('purchase_order_items', $purchaseOrderIndex),
                'product_id' => $this->id('products', $purchaseOrderIndex),
                'location_id' => $this->id('locations', $purchaseOrderIndex),
                'put_away_location_id' => $this->id('locations', $purchaseOrderIndex),
                'batch_number' => 'DUM-BATCH-'.$this->pad($purchaseOrderIndex),
                'expired_date' => $this->expiryDate($purchaseOrderIndex),
                'qty_good' => 10 + ($purchaseOrderIndex % 90),
                'qty_rejected' => 0,
                'status' => $index % 3 === 0 ? ReceiptItem::STATUS_STAGED : ReceiptItem::STATUS_PUT_AWAY,
                'notes' => 'Baris penerimaan dummy '.$index,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('receipt_items', $rows);
    }

    private function seedInventory(): void
    {
        $this->seedInventoryStocks();
        $this->seedStockMovements();
        $this->seedStockTransfers();
    }

    private function seedInventoryStocks(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('inventory_stocks', $index),
                'location_id' => $this->id('locations', $index),
                'product_id' => $this->id('products', $index),
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'expired_date' => $this->expiryDate($index),
                'received_at' => $this->timestampFor($index),
                'qty_on_hand' => 500 + ($index % 100),
                'qty_reserved' => 10 + ($index % 20),
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('inventory_stocks', $rows);
    }

    private function seedStockMovements(): void
    {
        $types = [
            StockMovement::TYPE_INBOUND,
            StockMovement::TYPE_OUTBOUND,
            StockMovement::TYPE_TRANSFER_IN,
            StockMovement::TYPE_TRANSFER_OUT,
            StockMovement::TYPE_ADJUSTMENT_IN,
            StockMovement::TYPE_ADJUSTMENT_OUT,
            StockMovement::TYPE_RETURN,
        ];
        $outboundTypes = [
            StockMovement::TYPE_OUTBOUND,
            StockMovement::TYPE_TRANSFER_OUT,
            StockMovement::TYPE_ADJUSTMENT_OUT,
        ];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $type = $types[($index - 1) % count($types)];
            $quantity = 5 + ($index % 50);

            $rows[] = [
                'id' => $this->id('stock_movements', $index),
                'product_id' => $this->id('products', $index),
                'location_id' => $this->id('locations', $index),
                'user_id' => $this->userId($index),
                'type' => $type,
                'quantity' => in_array($type, $outboundTypes, true) ? -$quantity : $quantity,
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'expired_date' => $this->expiryDate($index),
                'reference_no' => 'DUM-MOV-'.$this->pad($index),
                'notes' => 'Pergerakan stok dummy '.$index,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_movements', $rows);
    }

    private function seedStockTransfers(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $destinationIndex = ($index % self::COUNT) + 1;

            $rows[] = [
                'id' => $this->id('stock_transfers', $index),
                'reference_no' => 'DUM-TRF-'.$this->pad($index),
                'product_id' => $this->id('products', $index),
                'from_location_id' => $this->id('locations', $index),
                'to_location_id' => $this->id('locations', $destinationIndex),
                'quantity' => 5 + ($index % 45),
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'expired_date' => $this->expiryDate($index),
                'status' => StockTransfer::STATUS_COMPLETED,
                'notes' => 'Transfer stok dummy '.$index,
                'created_by' => $this->userId($index),
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_transfers', $rows);
    }

    private function seedSales(): void
    {
        $this->seedSalesOrders();
        $this->seedSalesOrderItems();
        $this->seedStockAllocations();
        $this->seedShipments();
    }

    private function seedSalesOrders(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $status = $index % 10 < 7 ? SalesOrder::STATUS_SHIPPED : SalesOrder::STATUS_PACKED;

            $rows[] = [
                'id' => $this->id('sales_orders', $index),
                'reference_no' => 'DUM-SO-'.$this->pad($index),
                'customer_name' => 'Pelanggan Dummy '.$this->pad($index),
                'customer_phone' => '0812-'.$this->pad($index),
                'status' => $status,
                'notes' => 'Sales order dummy '.$index,
                'created_by' => $this->userId($index),
                'reserved_at' => $this->timestampFor($index),
                'packed_at' => $this->timestampFor($index),
                'shipped_at' => $status === SalesOrder::STATUS_SHIPPED ? $this->timestampFor($index) : null,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('sales_orders', $rows);
    }

    private function seedSalesOrderItems(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $quantity = 10 + ($index % 20);

            $rows[] = [
                'id' => $this->id('sales_order_items', $index),
                'sales_order_id' => $this->id('sales_orders', $index),
                'product_id' => $this->id('products', $index),
                'qty_ordered' => $quantity,
                'qty_allocated' => $quantity,
                'qty_picked' => $quantity,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('sales_order_items', $rows);
    }

    private function seedStockAllocations(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('stock_allocations', $index),
                'sales_order_item_id' => $this->id('sales_order_items', $index),
                'inventory_stock_id' => $this->id('inventory_stocks', $index),
                'quantity' => 10 + ($index % 20),
                'pick_sequence' => 1,
                'picked_at' => $this->timestampFor($index),
                'packed_at' => $this->timestampFor($index),
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_allocations', $rows);
    }

    private function seedShipments(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $status = $index % 10 < 7 ? SalesOrder::STATUS_SHIPPED : SalesOrder::STATUS_PACKED;

            $rows[] = [
                'id' => $this->id('shipments', $index),
                'sales_order_id' => $this->id('sales_orders', $index),
                'courier_id' => $this->id('couriers', $index),
                'tracking_number' => 'DUMTRK'.str_pad((string) $index, 10, '0', STR_PAD_LEFT),
                'packed_by' => $this->userId($index),
                'packed_at' => $this->timestampFor($index),
                'shipped_at' => $status === SalesOrder::STATUS_SHIPPED ? $this->timestampFor($index) : null,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('shipments', $rows);
    }

    private function seedStockControl(): void
    {
        $this->seedStockOpnames();
        $this->seedStockOpnameItems();
        $this->seedStockAdjustments();
    }

    private function seedStockOpnames(): void
    {
        $zones = ['A', 'B', 'C', 'D', 'E'];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $status = $index % 3 === 0 ? StockOpname::STATUS_COMPLETED : StockOpname::STATUS_COUNTING;

            $rows[] = [
                'id' => $this->id('stock_opnames', $index),
                'reference_no' => 'DUM-OPN-'.$this->pad($index),
                'warehouse_id' => $this->id('warehouses', $index),
                'zone' => $zones[($index - 1) % count($zones)],
                'scheduled_date' => $this->now->copy()->addDays(($index % 60) - 30)->toDateString(),
                'status' => $status,
                'notes' => 'Stock opname dummy '.$index,
                'created_by' => $this->userId($index),
                'completed_at' => $status === StockOpname::STATUS_COMPLETED ? $this->timestampFor($index) : null,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_opnames', $rows);
    }

    private function seedStockOpnameItems(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $systemQuantity = 500 + ($index % 100);
            $difference = $this->opnameDifference($index);

            $rows[] = [
                'id' => $this->id('stock_opname_items', $index),
                'stock_opname_id' => $this->id('stock_opnames', $index),
                'product_id' => $this->id('products', $index),
                'location_id' => $this->id('locations', $index),
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'system_qty' => $systemQuantity,
                'counted_qty' => $systemQuantity + $difference,
                'difference' => $difference,
                'counted_by' => $this->userId($index),
                'counted_at' => $this->timestampFor($index),
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_opname_items', $rows);
    }

    private function seedStockAdjustments(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $status = match (true) {
                $index % 10 <= 4 => StockAdjustment::STATUS_PENDING,
                $index % 10 <= 8 => StockAdjustment::STATUS_APPROVED,
                default => StockAdjustment::STATUS_REJECTED,
            };
            $decided = $status !== StockAdjustment::STATUS_PENDING;

            $rows[] = [
                'id' => $this->id('stock_adjustments', $index),
                'reference_no' => 'DUM-ADJ-'.$this->pad($index),
                'stock_opname_item_id' => $this->id('stock_opname_items', $index),
                'product_id' => $this->id('products', $index),
                'location_id' => $this->id('locations', $index),
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'quantity_difference' => $this->opnameDifference($index),
                'reason' => 'Penyesuaian stok dummy '.$index,
                'status' => $status,
                'requested_by' => $this->userId($index),
                'approved_by' => $decided ? $this->adminId : null,
                'decided_at' => $decided ? $this->timestampFor($index) : null,
                'decision_note' => $decided ? 'Keputusan dummy '.$index : null,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('stock_adjustments', $rows);
    }

    private function seedReturns(): void
    {
        $this->seedReturnOrders();
        $this->seedReturnItems();
    }

    private function seedReturnOrders(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('return_orders', $index),
                'reference_no' => 'DUM-RET-'.$this->pad($index),
                'sales_order_id' => $this->id('sales_orders', $index),
                'customer_name' => 'Pelanggan Retur '.$this->pad($index),
                'status' => $index % 3 === 0 ? 'completed' : 'received',
                'notes' => 'Retur dummy '.$index,
                'received_by' => $this->userId($index),
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('return_orders', $rows);
    }

    private function seedReturnItems(): void
    {
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $disposition = match ($index % 4) {
                0 => ReturnItem::DISPOSITION_PENDING,
                1 => ReturnItem::DISPOSITION_RESTOCK,
                2 => ReturnItem::DISPOSITION_QUARANTINE,
                default => ReturnItem::DISPOSITION_SCRAP,
            };
            $decided = $disposition !== ReturnItem::DISPOSITION_PENDING;

            $rows[] = [
                'id' => $this->id('return_items', $index),
                'return_order_id' => $this->id('return_orders', $index),
                'product_id' => $this->id('products', $index),
                'quantity' => 1 + ($index % 10),
                'condition' => $index % 3 === 0 ? ReturnItem::CONDITION_DAMAGED : ReturnItem::CONDITION_GOOD,
                'disposition' => $disposition,
                'location_id' => $this->id('locations', $index),
                'batch_number' => 'DUM-BATCH-'.$this->pad($index),
                'notes' => 'Baris retur dummy '.$index,
                'decided_by' => $decided ? $this->adminId : null,
                'decided_at' => $decided ? $this->timestampFor($index) : null,
                ...$this->timestamps($index),
            ];
        }

        $this->insertRows('return_items', $rows);
    }

    private function seedAuditLogs(): void
    {
        $actions = ['created', 'updated', 'deleted', 'approved', 'rejected', 'login', 'export'];
        $rows = [];

        for ($index = 1; $index <= self::COUNT; $index++) {
            $rows[] = [
                'id' => $this->id('audit_logs', $index),
                'user_id' => $this->userId($index),
                'action' => $actions[($index - 1) % count($actions)],
                'auditable_type' => Product::class,
                'auditable_id' => $this->id('products', $index),
                'old_values' => json_encode(['index' => $index - 1], JSON_THROW_ON_ERROR),
                'new_values' => json_encode(['index' => $index], JSON_THROW_ON_ERROR),
                'ip_address' => '127.0.0.'.($index % 255),
                'user_agent' => 'DummyAgent/1.0',
                'created_at' => $this->timestampFor($index),
            ];
        }

        $this->insertRows('audit_logs', $rows);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function insertRows(string $table, array $rows): void
    {
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table($table)->insertOrIgnore($chunk);
        }
    }

    private function id(string $table, int $index): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, "cms-dummy:{$table}:{$index}")->toString();
    }

    private function userId(int $index): string
    {
        return $this->userIds[($index - 1) % count($this->userIds)];
    }

    private function pad(int $index): string
    {
        return str_pad((string) $index, 4, '0', STR_PAD_LEFT);
    }

    private function timestampFor(int $index): string
    {
        return $this->now->copy()
            ->subDays(($index * 37) % 365)
            ->subMinutes(($index * 13) % 1440)
            ->format('Y-m-d H:i:s');
    }

    /**
     * @return array{created_at: string, updated_at: string}
     */
    private function timestamps(int $index): array
    {
        $timestamp = $this->timestampFor($index);

        return [
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function expiryDate(int $index): ?string
    {
        if ($index % 5 === 0) {
            return null;
        }

        if ($index % 7 === 0) {
            return $this->now->copy()->subDays(($index % 30) + 1)->toDateString();
        }

        return $this->now->copy()->addDays(($index % 180) + 30)->toDateString();
    }

    private function opnameDifference(int $index): float
    {
        return ($index % 2 === 0 ? 1 : -1) * (1 + ($index % 10));
    }
}
