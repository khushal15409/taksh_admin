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
            if (!Schema::hasColumn('fm_rt_centers', 'owner_name')) {
                $table->string('owner_name', 191)->nullable()->after('center_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fm_rt_centers', function (Blueprint $table) {
            if (Schema::hasColumn('fm_rt_centers', 'owner_name')) {
                $table->dropColumn('owner_name');
            }
        });
    }
};




