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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_no')->unique();
            $table->foreignUuid('warehouse_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('zone')->nullable();
            $table->date('scheduled_date');
            $table->string('status', 20)->default('counting')->index();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
