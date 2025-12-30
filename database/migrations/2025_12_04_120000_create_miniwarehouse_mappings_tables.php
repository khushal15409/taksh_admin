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
        // Miniwarehouse to LM Center mapping (many-to-many)
        if (!Schema::hasTable('miniwarehouse_lm_center')) {
            Schema::create('miniwarehouse_lm_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('miniwarehouse_id');
                $table->unsignedBigInteger('lm_center_id');
                $table->timestamps();

                $table->foreign('miniwarehouse_id', 'mw_lm_mw_fk')->references('id')->on('miniwarehouses')->onDelete('cascade');
                $table->foreign('lm_center_id', 'mw_lm_lm_fk')->references('id')->on('lm_centers')->onDelete('cascade');
                $table->unique(['miniwarehouse_id', 'lm_center_id'], 'mw_lm_unique');
            });
        }

        // Miniwarehouse to FM/RT Center mapping (many-to-many)
        if (!Schema::hasTable('miniwarehouse_fm_rt_center')) {
            Schema::create('miniwarehouse_fm_rt_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('miniwarehouse_id');
                $table->unsignedBigInteger('fm_rt_center_id');
                $table->timestamps();

                $table->foreign('miniwarehouse_id', 'mw_fmrt_mw_fk')->references('id')->on('miniwarehouses')->onDelete('cascade');
                $table->foreign('fm_rt_center_id', 'mw_fmrt_fmrt_fk')->references('id')->on('fm_rt_centers')->onDelete('cascade');
                $table->unique(['miniwarehouse_id', 'fm_rt_center_id'], 'mw_fmrt_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miniwarehouse_fm_rt_center');
        Schema::dropIfExists('miniwarehouse_lm_center');
    }
};

