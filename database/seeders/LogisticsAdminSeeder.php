<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class LogisticsAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if logistics admin already exists
        $existingAdmin = DB::table('admins')->where('email', 'logistics@admin.com')->first();
        
        if (!$existingAdmin) {
            DB::table('admins')->insert([
                'f_name' => 'Logistics',
                'l_name' => 'Admin',
                'phone' => '01759412382',
                'email' => 'logistics@admin.com',
                'image' => 'def.png',
                'password' => Hash::make('12345678'),
                'remember_token' => Str::random(10),
                'role_id' => 1,
                'zone_id' => null,
                'is_logged_in' => 0,
                'is_logistics' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->command->info('Logistics admin user created successfully!');
            $this->command->info('Email: logistics@admin.com');
            $this->command->info('Password: 12345678');
        } else {
            // Update existing admin to be logistics
            DB::table('admins')
                ->where('email', 'logistics@admin.com')
                ->update([
                    'is_logistics' => 1,
                    'updated_at' => now()
                ]);
            
            $this->command->info('Existing admin updated to logistics user.');
        }
    }
}

