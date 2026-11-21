<?php

namespace Tests\Feature;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UuidPrimaryKeysTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_use_uuid_primary_keys(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Str::isUuid($user->id));
        $this->assertSame('string', $user->getKeyType());
        $this->assertFalse($user->getIncrementing());
    }

    public function test_wms_foreign_keys_reference_uuid_primary_keys(): void
    {
        $warehouse = Warehouse::factory()->create();
        $location = Location::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();
        $stock = InventoryStock::factory()->create([
            'location_id' => $location->id,
            'product_id' => $product->id,
        ]);

        $this->assertTrue(Str::isUuid($warehouse->id));
        $this->assertTrue(Str::isUuid($location->id));
        $this->assertTrue(Str::isUuid($product->id));
        $this->assertTrue(Str::isUuid($stock->id));
        $this->assertTrue(Str::isUuid($stock->location_id));
        $this->assertTrue(Str::isUuid($stock->product_id));
        $this->assertTrue($stock->product->is($product));
        $this->assertTrue($stock->location->is($location));
    }
}
