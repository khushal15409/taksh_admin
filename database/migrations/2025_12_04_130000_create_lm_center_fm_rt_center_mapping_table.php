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
        // LM Center to FM/RT Center mapping (many-to-many)
        if (!Schema::hasTable('lm_center_fm_rt_center')) {
            Schema::create('lm_center_fm_rt_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lm_center_id');
                $table->unsignedBigInteger('fm_rt_center_id');
                $table->timestamps();

                $table->foreign('lm_center_id')->references('id')->on('lm_centers')->onDelete('cascade');
                $table->foreign('fm_rt_center_id')->references('id')->on('fm_rt_centers')->onDelete('cascade');
                $table->unique(['lm_center_id', 'fm_rt_center_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lm_center_fm_rt_center');
    }
};

