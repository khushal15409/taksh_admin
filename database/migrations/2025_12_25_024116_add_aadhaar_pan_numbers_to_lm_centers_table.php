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
            if (!Schema::hasColumn('lm_centers', 'aadhaar_number')) {
                $table->string('aadhaar_number', 20)->nullable()->after('aadhaar_card');
            }
            if (!Schema::hasColumn('lm_centers', 'pan_card_number')) {
                $table->string('pan_card_number', 20)->nullable()->after('pan_card');
            }
            if (!Schema::hasColumn('lm_centers', 'aadhaar_verified')) {
                $table->boolean('aadhaar_verified')->default(0)->after('aadhaar_number');
            }
            if (!Schema::hasColumn('lm_centers', 'pan_verified')) {
                $table->boolean('pan_verified')->default(0)->after('pan_card_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lm_centers', function (Blueprint $table) {
            if (Schema::hasColumn('lm_centers', 'aadhaar_number')) {
                $table->dropColumn('aadhaar_number');
            }
            if (Schema::hasColumn('lm_centers', 'pan_card_number')) {
                $table->dropColumn('pan_card_number');
            }
            if (Schema::hasColumn('lm_centers', 'aadhaar_verified')) {
                $table->dropColumn('aadhaar_verified');
            }
            if (Schema::hasColumn('lm_centers', 'pan_verified')) {
                $table->dropColumn('pan_verified');
            }
        });
    }
};
