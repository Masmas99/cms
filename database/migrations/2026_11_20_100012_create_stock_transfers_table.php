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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_no')->unique();
            $table->foreignUuid('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('from_location_id')->constrained('locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('to_location_id')->constrained('locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('batch_number')->default('');
            $table->date('expired_date')->nullable();
            $table->string('status', 20)->default('completed')->index();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
