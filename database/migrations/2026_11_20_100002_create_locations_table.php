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
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('zone', 30);
            $table->string('aisle', 30);
            $table->string('rack', 30);
            $table->string('bin_code', 30);
            $table->string('full_code', 120)->unique();
            $table->decimal('capacity_m3', 10, 2)->default(0);
            $table->decimal('capacity_kg', 10, 2)->default(0);
            $table->boolean('is_staging')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['warehouse_id', 'zone', 'aisle', 'rack', 'bin_code'], 'locations_coordinates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
