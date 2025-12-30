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
        Schema::table('lm_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('lm_centers', 'email')) {
                $table->string('email', 191)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('lm_centers', 'mobile_number')) {
                $table->string('mobile_number', 20)->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lm_centers', function (Blueprint $table) {
            if (Schema::hasColumn('lm_centers', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('lm_centers', 'mobile_number')) {
                $table->dropColumn('mobile_number');
            }
        });
    }
};
