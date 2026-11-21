<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DummyDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_one_thousand_dummy_rows_per_feature_without_duplicates(): void
    {
        User::factory()->admin()->create();

        $expected = [
            'warehouses' => 1000,
            'locations' => 1000,
            'products' => 1000,
            'suppliers' => 1000,
            'couriers' => 1000,
            'purchase_orders' => 1000,
            'purchase_order_items' => 1000,
            'receipts' => 1000,
            'receipt_items' => 1000,
            'inventory_stocks' => 1000,
            'stock_movements' => 1000,
            'stock_transfers' => 1000,
            'sales_orders' => 1000,
            'sales_order_items' => 1000,
            'stock_allocations' => 1000,
            'shipments' => 1000,
            'stock_opnames' => 1000,
            'stock_opname_items' => 1000,
            'stock_adjustments' => 1000,
            'return_orders' => 1000,
            'return_items' => 1000,
            'audit_logs' => 1000,
        ];

        $this->seed(DummyDataSeeder::class);

        foreach ($expected as $table => $count) {
            $this->assertSame($count, DB::table($table)->count(), "Tabel {$table} tidak berisi {$count} data dummy.");
        }

        $this->seed(DummyDataSeeder::class);

        foreach ($expected as $table => $count) {
            $this->assertSame($count, DB::table($table)->count(), "Tabel {$table} bertambah duplikat saat seeder dijalankan ulang.");
        }

        $this->actingAs(User::query()->where('role', User::ROLE_ADMIN)->firstOrFail());

        foreach ([
            'admin.wms.products.index',
            'admin.wms.warehouses.index',
            'admin.wms.locations.index',
            'admin.wms.suppliers.index',
            'admin.wms.couriers.index',
            'admin.wms.purchase-orders.index',
            'admin.wms.sales-orders.index',
            'admin.wms.stocks.index',
            'admin.wms.transfers.index',
            'admin.wms.opnames.index',
            'admin.wms.adjustments.index',
            'admin.wms.returns.index',
            'admin.wms.audit.index',
            'admin.wms.reports.ledger',
            'admin.wms.reports.aging',
            'admin.wms.reports.capacity',
            'admin.wms.purchase-orders.create',
            'admin.wms.sales-orders.create',
            'admin.wms.transfers.create',
            'admin.wms.opnames.create',
            'admin.wms.adjustments.create',
            'admin.wms.returns.create',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }
}
