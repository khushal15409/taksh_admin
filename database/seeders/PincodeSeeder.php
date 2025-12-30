<?php

namespace Database\Seeders;

use App\Models\Pincode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PincodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/pincode.csv');
        
        if (!File::exists($csvPath)) {
            $this->command->error('CSV file not found at: ' . $csvPath);
            return;
        }

        $this->command->info('Clearing pincodes table to reset IDs...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table to reset IDs starting from 1
        DB::table('pincodes')->truncate();
        
        // Reset auto-increment to start from 1
        DB::statement('ALTER TABLE pincodes AUTO_INCREMENT = 1');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Reading CSV file...');
        
        $file = fopen($csvPath, 'r');
        
        if ($file === false) {
            $this->command->error('Unable to open CSV file.');
            return;
        }

        // Skip the header row
        $header = fgetcsv($file);
        
        if ($header === false) {
            $this->command->error('CSV file is empty or invalid.');
            fclose($file);
            return;
        }

        $batchSize = 1000;
        $batch = [];
        $totalInserted = 0;
        $totalSkipped = 0;
        $lineNumber = 1;
        
        // Track unique pincodes to avoid duplicates
        $uniquePincodes = [];

        $this->command->info('Starting to insert unique pincode data...');

        // Disable query log for better performance
        DB::connection()->disableQueryLog();

        while (($row = fgetcsv($file)) !== false) {
            $lineNumber++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map CSV columns to database fields
            $pincode = $this->cleanValue($row[4] ?? null);

            // Skip if pincode is empty
            if (empty($pincode)) {
                continue;
            }

            // Check if pincode already exists (case-insensitive check)
            $pincodeKey = strtolower(trim($pincode));
            
            if (isset($uniquePincodes[$pincodeKey])) {
                $totalSkipped++;
                continue; // Skip duplicate pincode
            }

            // Mark this pincode as seen
            $uniquePincodes[$pincodeKey] = true;

            $data = [
                'circlename' => $this->cleanValue($row[0] ?? null),
                'regionname' => $this->cleanValue($row[1] ?? null),
                'divisionname' => $this->cleanValue($row[2] ?? null),
                'officename' => $this->cleanValue($row[3] ?? null),
                'pincode' => $pincode,
                'officetype' => $this->cleanValue($row[5] ?? null),
                'delivery' => $this->cleanValue($row[6] ?? null),
                'district' => $this->cleanValue($row[7] ?? null),
                'statename' => $this->cleanValue($row[8] ?? null),
                'latitude' => $this->parseCoordinate($row[9] ?? null),
                'longitude' => $this->parseCoordinate($row[10] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $batch[] = $data;

            // Insert in batches for better performance
            if (count($batch) >= $batchSize) {
                try {
                    DB::table('pincodes')->insert($batch);
                    $totalInserted += count($batch);
                    $this->command->info("Inserted {$totalInserted} unique records... (Skipped {$totalSkipped} duplicates)");
                    $batch = [];
                } catch (\Exception $e) {
                    $this->command->error("Error inserting batch at line {$lineNumber}: " . $e->getMessage());
                }
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            try {
                DB::table('pincodes')->insert($batch);
                $totalInserted += count($batch);
            } catch (\Exception $e) {
                $this->command->error("Error inserting final batch: " . $e->getMessage());
            }
        }

        fclose($file);

        $this->command->info("Successfully inserted {$totalInserted} unique pincode records.");
        if ($totalSkipped > 0) {
            $this->command->info("Skipped {$totalSkipped} duplicate pincode records.");
        }
    }

    /**
     * Clean and trim CSV value
     *
     * @param mixed $value
     * @return string|null
     */
    private function cleanValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = trim($value);
        
        // Remove quotes if present
        $cleaned = trim($cleaned, '"');
        
        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Parse coordinate value (latitude/longitude)
     * Handles "NA" values and converts to null
     *
     * @param mixed $value
     * @return float|null
     */
    private function parseCoordinate($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = trim($value);
        $cleaned = trim($cleaned, '"');
        
        // Handle "NA" values
        if (strtoupper($cleaned) === 'NA' || $cleaned === '') {
            return null;
        }

        $floatValue = (float) $cleaned;
        
        // Return null if conversion resulted in 0 and original was not "0"
        if ($floatValue == 0 && $cleaned !== '0' && $cleaned !== '0.0') {
            return null;
        }

        return $floatValue;
    }
}


