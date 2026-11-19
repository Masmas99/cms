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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('batch_number')->default('');
            $table->date('expired_date')->nullable()->index();
            $table->dateTime('received_at');
            $table->decimal('qty_on_hand', 12, 2)->default(0);
            $table->decimal('qty_reserved', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['location_id', 'product_id', 'batch_number'], 'inventory_stocks_lot_unique');
            $table->index(['product_id', 'expired_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
