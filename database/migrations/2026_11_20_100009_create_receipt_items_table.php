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
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('purchase_order_item_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('put_away_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('batch_number')->default('');
            $table->date('expired_date')->nullable();
            $table->decimal('qty_good', 12, 2)->default(0);
            $table->decimal('qty_rejected', 12, 2)->default(0);
            $table->string('status', 20)->default('staged')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};
