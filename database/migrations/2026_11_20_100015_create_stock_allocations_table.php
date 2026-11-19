<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_stock_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->unsignedInteger('pick_sequence')->default(0);
            $table->dateTime('picked_at')->nullable();
            $table->dateTime('packed_at')->nullable();
            $table->timestamps();

            $table->unique(['sales_order_item_id', 'inventory_stock_id'], 'stock_allocations_lot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_allocations');
    }
};
