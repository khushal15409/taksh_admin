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
            if (!Schema::hasColumn('fm_rt_centers', 'email')) {
                $table->string('email', 191)->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('fm_rt_centers', 'mobile_number')) {
                $table->string('mobile_number', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('fm_rt_centers', 'images')) {
                $table->json('images')->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fm_rt_centers', function (Blueprint $table) {
            if (Schema::hasColumn('fm_rt_centers', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('fm_rt_centers', 'mobile_number')) {
                $table->dropColumn('mobile_number');
            }
            if (Schema::hasColumn('fm_rt_centers', 'images')) {
                $table->dropColumn('images');
            }
        });
    }
};
