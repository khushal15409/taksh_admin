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
        Schema::create('pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('circlename')->nullable();
            $table->string('regionname')->nullable();
            $table->string('divisionname')->nullable();
            $table->string('officename')->nullable();
            $table->string('pincode', 10)->index();
            $table->string('officetype')->nullable();
            $table->string('delivery')->nullable();
            $table->string('district')->nullable();
            $table->string('statename')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pincodes');
    }
};
