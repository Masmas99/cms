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
        Schema::create('return_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('return_order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('condition', 20)->default('good');
            $table->string('disposition', 20)->default('pending')->index();
            $table->foreignUuid('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number')->default('');
            $table->text('notes')->nullable();
            $table->foreignUuid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
