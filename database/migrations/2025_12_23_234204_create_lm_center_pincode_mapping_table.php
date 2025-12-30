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
        // LM Center to Pincode mapping (many-to-many)
        if (!Schema::hasTable('lm_center_pincode')) {
            Schema::create('lm_center_pincode', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lm_center_id');
                $table->unsignedBigInteger('pincode_id');
                $table->timestamps();

                $table->foreign('lm_center_id')->references('id')->on('lm_centers')->onDelete('cascade');
                $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('cascade');
                $table->unique(['lm_center_id', 'pincode_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lm_center_pincode');
    }
};
