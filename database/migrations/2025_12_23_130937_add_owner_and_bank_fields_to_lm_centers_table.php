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
            if (!Schema::hasColumn('lm_centers', 'owner_pincode')) {
                $table->string('owner_pincode', 20)->nullable()->after('owner_address');
            }
            if (!Schema::hasColumn('lm_centers', 'owner_latitude')) {
                $table->string('owner_latitude', 50)->nullable()->after('owner_pincode');
            }
            if (!Schema::hasColumn('lm_centers', 'owner_longitude')) {
                $table->string('owner_longitude', 50)->nullable()->after('owner_latitude');
            }
            if (!Schema::hasColumn('lm_centers', 'owner_mobile')) {
                $table->string('owner_mobile', 20)->nullable()->after('owner_longitude');
            }
            if (!Schema::hasColumn('lm_centers', 'owner_email')) {
                $table->string('owner_email', 191)->nullable()->after('owner_mobile');
            }
            if (!Schema::hasColumn('lm_centers', 'aadhaar_card')) {
                $table->string('aadhaar_card', 191)->nullable()->after('owner_email');
            }
            if (!Schema::hasColumn('lm_centers', 'pan_card')) {
                $table->string('pan_card', 191)->nullable()->after('aadhaar_card');
            }
            if (!Schema::hasColumn('lm_centers', 'bank_name')) {
                $table->string('bank_name', 191)->nullable()->after('pan_card');
            }
            if (!Schema::hasColumn('lm_centers', 'bank_account_number')) {
                $table->string('bank_account_number', 191)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('lm_centers', 'bank_ifsc_code')) {
                $table->string('bank_ifsc_code', 50)->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('lm_centers', 'bank_branch')) {
                $table->string('bank_branch', 191)->nullable()->after('bank_ifsc_code');
            }
            if (!Schema::hasColumn('lm_centers', 'bank_holder_name')) {
                $table->string('bank_holder_name', 191)->nullable()->after('bank_branch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lm_centers', function (Blueprint $table) {
            $columns = [
                'owner_pincode', 'owner_latitude', 'owner_longitude', 'owner_mobile', 'owner_email',
                'aadhaar_card', 'pan_card', 'bank_name', 'bank_account_number', 
                'bank_ifsc_code', 'bank_branch', 'bank_holder_name'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('lm_centers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
