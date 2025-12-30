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
        // Warehouse to Miniwarehouse mapping (many-to-many)
        if (!Schema::hasTable('warehouse_miniwarehouse')) {
            Schema::create('warehouse_miniwarehouse', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('miniwarehouse_id');
                $table->timestamps();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('miniwarehouse_id')->references('id')->on('miniwarehouses')->onDelete('cascade');
                $table->unique(['warehouse_id', 'miniwarehouse_id']);
            });
        }

        // Warehouse to LM Center mapping (many-to-many)
        if (!Schema::hasTable('warehouse_lm_center')) {
            Schema::create('warehouse_lm_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('lm_center_id');
                $table->timestamps();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('lm_center_id')->references('id')->on('lm_centers')->onDelete('cascade');
                $table->unique(['warehouse_id', 'lm_center_id']);
            });
        }

        // Warehouse to FM/RT Center mapping (many-to-many)
        if (!Schema::hasTable('warehouse_fm_rt_center')) {
            Schema::create('warehouse_fm_rt_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedBigInteger('fm_rt_center_id');
                $table->timestamps();

                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('fm_rt_center_id')->references('id')->on('fm_rt_centers')->onDelete('cascade');
                $table->unique(['warehouse_id', 'fm_rt_center_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_fm_rt_center');
        Schema::dropIfExists('warehouse_lm_center');
        Schema::dropIfExists('warehouse_miniwarehouse');
    }
};

