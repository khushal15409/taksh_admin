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
        // Warehouse to Warehouse mapping (many-to-many)
        if (!Schema::hasTable('warehouse_warehouse')) {
            Schema::create('warehouse_warehouse', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('mapped_warehouse_id');
                $table->timestamps();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('mapped_warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->unique(['warehouse_id', 'mapped_warehouse_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_warehouse');
    }
};
