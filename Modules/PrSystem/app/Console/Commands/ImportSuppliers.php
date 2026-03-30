<?php

namespace Modules\PrSystem\Console\Commands;

use Illuminate\Console\Command;

class ImportSuppliers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-suppliers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import suppliers from CSV file';

    public function handle()
    {
        $file = base_path('DAFTAR SUPPLIER PT.SSM.csv');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $this->info("Importing suppliers from $file...");

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Could not open file.");
            return;
        }
        
        // Clean up previous CLI imports to prevent duplicates/garbage
        $deleted = \Modules\PrSystem\Models\Vendor::where('description', 'LIKE', 'Imported via CLI%')->delete();
        if ($deleted > 0) {
            $this->info("Cleaned up $deleted previously imported records.");
        }

        // Read header
        $header = fgetcsv($handle, 1000, ',');
        
        $count = 0;
        $skipped = 0;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {

            $name = $data[1] ?? null;
            if (!$name || trim($name) == '') continue;

            // Trim fields to avoid DB truncation errors
            $name = substr(trim($name), 0, 255);
            $descCsv = isset($data[2]) ? trim($data[2]) : '';
            $category = isset($data[3]) ? substr(trim($data[3]), 0, 255) : null;
            $location = isset($data[4]) ? substr(trim($data[4]), 0, 255) : null;
            $address = isset($data[5]) ? substr(trim($data[5]), 0, 500) : null; 
            $pic = isset($data[6]) ? substr(trim($data[6]), 0, 255) : null;
            $adminPhone = isset($data[7]) ? trim($data[7]) : null;
            $email = isset($data[8]) ? substr(trim($data[8]), 0, 255) : null;
            
            $statusRaw = isset($data[9]) ? strtoupper(trim($data[9])) : '';
            
            // Status Logic
            $status = 'Belum Transaksi';
            if (str_contains($statusRaw, 'NO') || str_contains($statusRaw, 'BELUM') || str_contains($statusRaw, 'NON')) {
                $status = 'Belum Transaksi';
            } elseif (str_contains($statusRaw, 'TRANSAKSI')) {
                $status = 'Pernah Transaksi';
            }

            $code = $data[0] ?? '-'; 
            
            // Build Description
            $fullDescription = $descCsv;
            $fullDescription .= ". Imported via CLI. Code: " . $code;

            // Check existence by Name
            $exists = \Modules\PrSystem\Models\Vendor::where('name', $name)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                \Modules\PrSystem\Models\Vendor::create([
                    'name' => $name,
                    'address' => $address,
                    'location' => $location,
                    'phone' => null,
                    'admin_phone' => $adminPhone,
                    'email' => $email,
                    'pic_name' => $pic,
                    'category' => $category,
                    'status' => $status,
                    'description' => substr($fullDescription, 0, 1000),
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to insert $name: " . $e->getMessage());
            }
        }

        fclose($handle);

        $this->info("Import completed. Added: $count, Skipped: $skipped");
    }
}
