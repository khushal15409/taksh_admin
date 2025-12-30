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
        if (!Schema::hasColumn('warehouses', 'owner_name')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->string('owner_name', 100)->default('taksh')->after('name');
            });
        }

        if (!Schema::hasColumn('miniwarehouses', 'owner_name')) {
            Schema::table('miniwarehouses', function (Blueprint $table) {
                $table->string('owner_name', 100)->default('taksh')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('owner_name');
        });

        Schema::table('miniwarehouses', function (Blueprint $table) {
            $table->dropColumn('owner_name');
        });
    }
};

