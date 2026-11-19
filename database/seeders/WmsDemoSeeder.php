<?php

namespace Database\Seeders;

use App\Models\Courier;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WmsDemoSeeder extends Seeder
{
    /**
     * Seed a small but realistic warehouse so every screen has data to show.
     */
    public function run(InventoryService $inventory): void
    {
        $admin = User::query()->where('role', User::ROLE_ADMIN)->first()
            ?? User::query()->first();

        $warehouse = Warehouse::query()->firstOrCreate(
            ['code' => 'WH-JKT'],
            [
                'name' => 'Gudang Jakarta',
                'address' => 'Jl. Raya Cakung Cilincing No. 18',
                'city' => 'Jakarta Utara',
                'is_active' => true,
            ],
        );

        $staging = Location::query()->firstOrCreate(
            ['full_code' => 'WH-JKT-STG-IN'],
            [
                'warehouse_id' => $warehouse->id,
                'zone' => 'STG',
                'aisle' => 'STG',
                'rack' => 'STG',
                'bin_code' => 'IN',
                'capacity_m3' => 0,
                'capacity_kg' => 0,
                'is_staging' => true,
                'is_active' => true,
            ],
        );

        $bins = collect([
            ['WH-JKT-A-01-01', 'A', '01', '01', '01'],
            ['WH-JKT-A-01-02', 'A', '01', '01', '02'],
            ['WH-JKT-A-02-01', 'A', '02', '01', '01'],
            ['WH-JKT-B-01-01', 'B', '01', '01', '01'],
            ['WH-JKT-B-01-02', 'B', '01', '01', '02'],
            ['WH-JKT-B-02-01', 'B', '02', '01', '01'],
        ])->mapWithKeys(function (array $row) use ($warehouse): array {
            [$fullCode, $zone, $aisle, $rack, $bin] = $row;

            $location = Location::query()->firstOrCreate(
                ['full_code' => $fullCode],
                [
                    'warehouse_id' => $warehouse->id,
                    'zone' => $zone,
                    'aisle' => $aisle,
                    'rack' => $rack,
                    'bin_code' => $bin,
                    'capacity_m3' => 2.5,
                    'capacity_kg' => 250,
                    'is_staging' => false,
                    'is_active' => true,
                ],
            );

            return [$fullCode => $location];
        });

        $products = collect([
            [
                'sku' => 'RT-CBL-001',
                'barcode' => '8991000000011',
                'name' => 'Kabel UTP Cat6 305m',
                'category' => 'Jaringan',
                'uom' => 'roll',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 5,
                'max_stock' => 40,
                'unit_cost' => 1250000,
                'weight_kg' => 12.5,
            ],
            [
                'sku' => 'RT-RTR-002',
                'barcode' => '8991000000028',
                'name' => 'Router Industri 4G',
                'category' => 'Perangkat',
                'uom' => 'unit',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 4,
                'max_stock' => 20,
                'unit_cost' => 2750000,
                'weight_kg' => 1.2,
            ],
            [
                'sku' => 'RT-SWT-003',
                'barcode' => '8991000000035',
                'name' => 'Switch 8 Port Gigabit',
                'category' => 'Perangkat',
                'uom' => 'unit',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 5,
                'max_stock' => 30,
                'unit_cost' => 850000,
                'weight_kg' => 0.9,
            ],
            [
                'sku' => 'RT-PWR-004',
                'barcode' => '8991000000042',
                'name' => 'Power Supply 12V 5A',
                'category' => 'Aksesoris',
                'uom' => 'pcs',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 10,
                'max_stock' => 100,
                'unit_cost' => 185000,
                'weight_kg' => 0.4,
            ],
            [
                'sku' => 'RT-CON-005',
                'barcode' => '8991000000059',
                'name' => 'Konektor RJ45 100 pcs',
                'category' => 'Aksesoris',
                'uom' => 'pack',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 8,
                'max_stock' => 80,
                'unit_cost' => 95000,
                'weight_kg' => 0.3,
            ],
            [
                'sku' => 'RT-SFP-006',
                'barcode' => '8991000000066',
                'name' => 'Modul SFP 1G Multimode',
                'category' => 'Perangkat',
                'uom' => 'pcs',
                'rotation' => Product::ROTATION_FEFO,
                'min_stock' => 6,
                'max_stock' => 50,
                'unit_cost' => 420000,
                'weight_kg' => 0.1,
            ],
            [
                'sku' => 'RT-BAT-007',
                'barcode' => '8991000000073',
                'name' => 'Baterai UPS 12V 9Ah',
                'category' => 'Peralatan',
                'uom' => 'unit',
                'rotation' => Product::ROTATION_FEFO,
                'min_stock' => 10,
                'max_stock' => 60,
                'unit_cost' => 650000,
                'weight_kg' => 2.7,
            ],
            [
                'sku' => 'RT-TIE-008',
                'barcode' => '8991000000080',
                'name' => 'Cable Tie 30cm 100 pcs',
                'category' => 'Aksesoris',
                'uom' => 'pack',
                'rotation' => Product::ROTATION_FIFO,
                'min_stock' => 15,
                'max_stock' => 150,
                'unit_cost' => 35000,
                'weight_kg' => 0.2,
            ],
        ])->mapWithKeys(function (array $attributes): array {
            $product = Product::query()->firstOrCreate(
                ['sku' => $attributes['sku']],
                $attributes + ['is_active' => true],
            );

            return [$attributes['sku'] => $product];
        });

        $suppliers = collect([
            [
                'code' => 'SUP-001',
                'name' => 'PT Sinar Jaringan Nusantara',
                'contact_person' => 'Andi Pratama',
                'phone' => '021-5550101',
                'email' => 'sales@sinarjaringan.test',
                'address' => 'Jl. Industri Raya No. 42, Tangerang',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'CV Mitra Kabel Indonesia',
                'contact_person' => 'Siti Rahma',
                'phone' => '021-5550202',
                'email' => 'order@mitrakabel.test',
                'address' => 'Jl. Gatot Subroto No. 8, Bekasi',
            ],
        ])->map(fn (array $attributes): Supplier => Supplier::query()->firstOrCreate(
            ['code' => $attributes['code']],
            $attributes,
        ));

        $couriers = collect([
            ['code' => 'JNE', 'name' => 'JNE Reguler', 'tracking_url' => 'https://www.jne.co.id/tracking'],
            ['code' => 'JNT', 'name' => 'J&T Express', 'tracking_url' => 'https://www.jet.co.id/track'],
            ['code' => 'SIC', 'name' => 'SiCepat REG', 'tracking_url' => 'https://www.sicepat.com/checkAwb'],
        ])->each(fn (array $attributes) => Courier::query()->firstOrCreate(
            ['code' => $attributes['code']],
            $attributes + ['is_active' => true],
        ));

        if (app()->environment('local') && $admin !== null) {
            User::query()->firstOrCreate(
                ['email' => 'staff@rumitech.test'],
                [
                    'name' => 'Staff Gudang',
                    'password' => 'password',
                    'role' => User::ROLE_USER,
                    'email_verified_at' => now(),
                ],
            );
        }

        if ($products->isEmpty()) {
            return;
        }

        $this->seedOpeningStock($inventory, $admin, $bins, $products);
        $this->seedDocuments($admin, $warehouse, $suppliers, $products);
    }

    /**
     * @param  Collection<string, Location>  $bins
     * @param  Collection<string, Product>  $products
     */
    private function seedOpeningStock(
        InventoryService $inventory,
        ?User $admin,
        $bins,
        $products,
    ): void {
        if (StockMovement::query()->where('reference_no', 'SEED-OPENING')->exists()) {
            return;
        }

        $lots = [
            ['RT-CBL-001', 'WH-JKT-A-01-01', 'BATCH-CBL-2401', 60, null],
            ['RT-RTR-002', 'WH-JKT-A-01-02', 'BATCH-RTR-2401', 12, null],
            ['RT-SWT-003', 'WH-JKT-A-02-01', 'BATCH-SWT-2401', 18, null],
            ['RT-PWR-004', 'WH-JKT-B-01-01', 'BATCH-PWR-2401', 40, null],
            ['RT-CON-005', 'WH-JKT-B-01-02', 'BATCH-CON-2401', 25, null],
            ['RT-SFP-006', 'WH-JKT-B-02-01', 'BATCH-SFP-2401', 15, now()->addDays(45)],
            ['RT-BAT-007', 'WH-JKT-B-02-01', 'BATCH-BAT-2401', 9, now()->addDays(20)],
            ['RT-TIE-008', 'WH-JKT-B-01-02', 'BATCH-TIE-2401', 80, null],
        ];

        foreach ($lots as [$sku, $binCode, $batch, $quantity, $expiry]) {
            $product = $products->get($sku);
            $location = $bins->get($binCode);

            if ($product === null || $location === null) {
                continue;
            }

            $inventory->addStock(
                $product,
                $location,
                (float) $quantity,
                $batch,
                $expiry instanceof Carbon ? $expiry : null,
                $admin,
                StockMovement::TYPE_INBOUND,
                'SEED-OPENING',
                'Saldo awal data demo.',
            );
        }
    }

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<string, Product>  $products
     */
    private function seedDocuments(
        ?User $admin,
        Warehouse $warehouse,
        $suppliers,
        $products,
    ): void {
        $purchaseOrder = PurchaseOrder::query()->firstOrCreate(
            ['reference_no' => 'PO-DEMO-001'],
            [
                'supplier_id' => $suppliers->firstWhere('code', 'SUP-001')?->id ?? $suppliers->first()?->id,
                'warehouse_id' => $warehouse->id,
                'created_by' => $admin?->id,
                'expected_date' => now()->addDays(3)->toDateString(),
                'status' => PurchaseOrder::STATUS_SCHEDULED,
                'notes' => 'Pengadaan rutin kuartal ini.',
            ],
        );

        if ($purchaseOrder->items()->doesntExist()) {
            foreach ([['RT-CBL-001', 20], ['RT-RTR-002', 6], ['RT-SWT-003', 10]] as [$sku, $quantity]) {
                $product = $products->get($sku);

                if ($product === null) {
                    continue;
                }

                PurchaseOrderItem::query()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'qty_expected' => $quantity,
                    'qty_received' => 0,
                    'qty_rejected' => 0,
                ]);
            }
        }

        $salesOrder = SalesOrder::query()->firstOrCreate(
            ['reference_no' => 'SO-DEMO-001'],
            [
                'customer_name' => 'PT Mitra Jaringan',
                'customer_phone' => '0812-3456-7890',
                'status' => SalesOrder::STATUS_DRAFT,
                'notes' => 'Kirim ke kantor pusat, lantai 3.',
                'created_by' => $admin?->id,
            ],
        );

        if ($salesOrder->items()->doesntExist()) {
            foreach ([['RT-CBL-001', 5], ['RT-PWR-004', 10], ['RT-SFP-006', 4]] as [$sku, $quantity]) {
                $product = $products->get($sku);

                if ($product === null) {
                    continue;
                }

                SalesOrderItem::query()->create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $product->id,
                    'qty_ordered' => $quantity,
                    'qty_allocated' => 0,
                    'qty_picked' => 0,
                ]);
            }
        }
    }
}
