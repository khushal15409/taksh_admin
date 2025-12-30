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
        // FM/RT Center to Pincode mapping (many-to-many)
        if (!Schema::hasTable('fm_rt_center_pincode')) {
            Schema::create('fm_rt_center_pincode', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fm_rt_center_id');
                $table->unsignedBigInteger('pincode_id');
                $table->timestamps();

                $table->foreign('fm_rt_center_id')->references('id')->on('fm_rt_centers')->onDelete('cascade');
                $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('cascade');
                $table->unique(['fm_rt_center_id', 'pincode_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_rt_center_pincode');
    }
};
