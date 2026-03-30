<?php

namespace Modules\PrSystem\Console\Commands;

use Modules\PrSystem\Models\Product;
use Modules\PrSystem\Models\WarehouseStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWarehouseStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:warehouse-stock {file} {warehouse_id=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import stock from CSV to a specific warehouse';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $warehouseId = $this->argument('warehouse_id');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Starting import for Warehouse ID: $warehouseId from file: $file");

        $handle = fopen($file, 'r');
        
        // === SMART DELIMITER DETECTION ===
        // Read the first line to detect the delimiter used.
        // Priority: TAB > Semicolon > Comma
        // Using TAB or Semicolon as delimiter allows numbers like 362,131.00 
        // to be read as a single field (not split by the comma).
        $firstLine = fgets($handle);
        rewind($handle);

        $tabCount       = substr_count($firstLine, "\t");
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount     = substr_count($firstLine, ',');

        if ($tabCount >= $semicolonCount && $tabCount >= $commaCount && $tabCount > 0) {
            $delimiter = "\t";
            $this->info("Detected Delimiter: TAB");
        } elseif ($semicolonCount > $commaCount) {
            $delimiter = ';';
            $this->info("Detected Delimiter: Semicolon (;)");
        } else {
            $delimiter = ',';
            $this->info("Detected Delimiter: Comma (,) — WARNING: numbers with commas may be split!");
        }

        $header = fgetcsv($handle, 4096, $delimiter);
        
        // Detect Column Mapping
        // Check if first column is empty (Original CSV format) or ITEM ID (KDE CSV format)
        $firstCol = isset($header[0]) ? strtoupper(trim($header[0])) : '';
        // Remove BOM if present
        $firstCol = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $firstCol);
        
        $useZeroIndex = false;
        if (str_contains($firstCol, 'ITEM ID')) {
            $useZeroIndex = true;
            $this->info("Detected Format: Standard CSV (0-indexed)");
        } else {
            $this->info("Detected Format: Offset CSV (1-indexed)");
        }

        $idxCode = $useZeroIndex ? 0 : 1;
        $idxName = $useZeroIndex ? 1 : 2;
        $idxUnit = $useZeroIndex ? 2 : 3;
        $idxQty = $useZeroIndex ? 3 : 4;
        $idxPrice = $useZeroIndex ? 4 : 5;

        $count = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
                
                $code = trim($row[$idxCode] ?? '');
                if (empty($code)) {
                    continue; // Skip empty rows
                }

                $name = trim($row[$idxName] ?? '');
                $unit = trim($row[$idxUnit] ?? '');
                
                // Sanitize QTY
                $qtyRaw = $row[$idxQty] ?? 0;
                $qty = $this->sanitizeNumber($qtyRaw, true); // True implies convert to integer

                // Sanitize Price
                $priceRaw = $row[$idxPrice] ?? 0;
                $price = $this->sanitizeNumber($priceRaw, false);

                try {
                    // Update or Create Product
                    // We use updateOrCreate to ensure we catch existing items by code
                    $product = Product::updateOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'unit' => $unit,
                            'price_estimation' => $price,
                            // Set defaults for other required fields if they don't exist
                            'category' => $product->category ?? 'General', 
                            'min_stock' => $product->min_stock ?? 0,
                        ]
                    );

                    // Update Warehouse Stock
                    WarehouseStock::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                        ],
                        [
                            'quantity' => $qty,
                        ]
                    );

                    $count++;
                    if ($count % 100 == 0) {
                        $this->info("Processed $count records...");
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing row for code $code: " . $e->getMessage());
                    $errors++;
                }
            }

            DB::commit();
            $this->info("Import completed successfully! Format Used: " . ($useZeroIndex ? "Standard" : "Offset"));
            $this->info("Total processed: $count");
            $this->info("Total errors: $errors");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Fatal error during import: " . $e->getMessage());
            return 1;
        } finally {
            fclose($handle);
        }

        return 0;
    }

    private function sanitizeNumber($value, $isInteger = false)
    {
        $value = trim($value);

        // Handle "NaN" or "Infinity" or empty
        if (empty($value) || strcasecmp($value, 'NaN') === 0 || strcasecmp($value, 'Infinity') === 0) {
            return 0;
        }

        // Remove currency symbols, quotes, spaces
        $value = str_replace(['Rp', '"', "'", ' ', "\xA0"], '', $value);
        $value = trim($value);

        // If already a plain number, return directly
        if (is_numeric($value)) {
            return $isInteger ? (int)round((float)$value) : (float)$value;
        }

        // Determine number format:
        // Case 1: Anglo format  → 1,423.00  (dot = decimal, comma = thousands)
        // Case 2: ID/EU format  → 1.423,00  (comma = decimal, dot = thousands)
        $hasDot   = strpos($value, '.') !== false;
        $hasComma = strpos($value, ',') !== false;

        if ($hasDot && $hasComma) {
            // Whichever comes LAST is the decimal separator
            if (strrpos($value, '.') > strrpos($value, ',')) {
                // Anglo: 1,423.00 → remove commas, keep dot
                $clean = str_replace(',', '', $value);
            } else {
                // ID: 1.423,00 → remove dots, replace comma with dot
                $clean = str_replace('.', '', $value);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif ($hasComma && !$hasDot) {
            // Could be: 1,423 (thousands) or 1,50 (decimal)
            $parts = explode(',', $value);
            // If there are multiple commas, or last part has more than 2 digits → thousands sep
            if (count($parts) > 2 || strlen(end($parts)) != 2) {
                $clean = str_replace(',', '', $value);
            } else {
                // Treat comma as decimal
                $clean = str_replace(',', '.', $value);
            }
        } elseif ($hasDot && !$hasComma) {
            // Could be: 1.423 (ID thousands) or 1.50 (decimal)
            $parts = explode('.', $value);
            // If there are multiple dots, or last part has more than 2 digits → thousands sep
            if (count($parts) > 2 || strlen(end($parts)) != 2) {
                $clean = str_replace('.', '', $value);
            } else {
                $clean = $value; // already valid decimal
            }
        } else {
            $clean = $value;
        }

        if (is_numeric($clean)) {
            return $isInteger ? (int)round((float)$clean) : (float)$clean;
        }

        return 0;
    }
}
