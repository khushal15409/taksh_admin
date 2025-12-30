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
        if (!Schema::hasColumn('lm_centers', 'state')) {
            Schema::table('lm_centers', function (Blueprint $table) {
                $table->string('state', 191)->nullable()->after('longitude');
            });
        }
        if (!Schema::hasColumn('lm_centers', 'city')) {
            Schema::table('lm_centers', function (Blueprint $table) {
                $table->string('city', 191)->nullable()->after('state');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lm_centers', function (Blueprint $table) {
            $table->dropColumn(['state', 'city']);
        });
    }
};
