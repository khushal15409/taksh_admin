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
        Schema::create('fm_rt_centers', function (Blueprint $table) {
            $table->id();
            $table->string('center_name', 191);
            $table->text('full_address');
            $table->string('location', 191)->nullable();
            $table->string('pincode', 20);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('city', 191)->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_rt_centers');
    }
};
