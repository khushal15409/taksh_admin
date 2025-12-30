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
        Schema::table('fm_rt_centers', function (Blueprint $table) {
            // Change latitude and longitude from decimal to string to match warehouse table
            // and allow for proper coordinate values
            $table->string('latitude', 50)->nullable()->change();
            $table->string('longitude', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fm_rt_centers', function (Blueprint $table) {
            // Revert back to decimal if needed
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};
