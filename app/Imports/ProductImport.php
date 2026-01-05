<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected array $errors = [];

    /**
     * Map Excel row to Product model update
     */
    public function model(array $row)
    {
        // Get product ID from the row (using Arabic heading or column letter)
        $productId = $this->getValue($row, ['المعرف (id) - لا تعدل', 'id', 0]);

        if (empty($productId) || !is_numeric($productId)) {
            $this->skippedCount++;
            return null;
        }

        $product = Product::find($productId);

        if (!$product) {
            // Try to find by SKU
            $sku = $this->getValue($row, ['الكود (sku) - لا تعدل', 'sku', 1]);
            if ($sku) {
                $product = Product::where('sku', $sku)->first();
            }
        }

        if (!$product) {
            $this->errors[] = "المنتج بالمعرف {$productId} غير موجود";
            $this->skippedCount++;
            return null;
        }

        // Update product with new data
        $updateData = [];

        // Name
        $name = $this->getValue($row, ['الاسم', 'name', 2]);
        if (!empty($name) && !$this->isPlaceholder($name)) {
            $updateData['name'] = $name;
        }

        // Price
        $price = $this->getValue($row, ['السعر', 'price', 4]);
        if ($this->isValidNumber($price)) {
            $updateData['price'] = $this->cleanNumber($price);
        }

        // Sale Price
        $salePrice = $this->getValue($row, ['سعر التخفيض', 'sale_price', 5]);
        if ($this->isValidNumber($salePrice)) {
            $updateData['sale_price'] = $this->cleanNumber($salePrice);
        } elseif (empty($salePrice) || $salePrice === '-' || $salePrice === '0') {
            $updateData['sale_price'] = null;
        }

        // Cost Price
        $costPrice = $this->getValue($row, ['سعر التكلفة', 'cost_price', 6]);
        if ($this->isValidNumber($costPrice)) {
            $updateData['cost_price'] = $this->cleanNumber($costPrice);
        }

        // Stock
        $stock = $this->getValue($row, ['المخزون', 'stock', 7]);
        if ($this->isValidNumber($stock)) {
            $updateData['stock'] = (int) $this->cleanNumber($stock);
        }

        // Low Stock Threshold
        $lowStockThreshold = $this->getValue($row, ['حد المخزون المنخفض', 'low_stock_threshold', 8]);
        if ($this->isValidNumber($lowStockThreshold)) {
            $updateData['low_stock_threshold'] = (int) $this->cleanNumber($lowStockThreshold);
        }

        // Status
        $status = $this->getValue($row, ['الحالة (active/inactive/draft)', 'status', 9]);
        if (!empty($status) && in_array(strtolower($status), ['active', 'inactive', 'draft'])) {
            $updateData['status'] = strtolower($status);
        }

        if (!empty($updateData)) {
            $product->update($updateData);
            $this->updatedCount++;
            Log::info("Product updated via import", ['id' => $product->id, 'updates' => $updateData]);
        }

        return null; // We're updating, not creating
    }

    /**
     * Get value from row using multiple possible keys
     */
    protected function getValue(array $row, array $keys)
    {
        foreach ($keys as $key) {
            // Normalize key for comparison (lowercase, trim)
            $normalizedKey = is_string($key) ? mb_strtolower(trim($key)) : $key;

            foreach ($row as $rowKey => $value) {
                $normalizedRowKey = is_string($rowKey) ? mb_strtolower(trim($rowKey)) : $rowKey;

                if ($normalizedRowKey === $normalizedKey) {
                    return $value;
                }
            }

            // Direct key access
            if (isset($row[$key])) {
                return $row[$key];
            }
        }
        return null;
    }

    /**
     * Check if value is a placeholder
     */
    protected function isPlaceholder($value): bool
    {
        if (!is_string($value))
            return false;
        return str_contains($value, 'مثال') || str_contains($value, 'للمعلومات');
    }

    /**
     * Check if value is a valid number
     */
    protected function isValidNumber($value): bool
    {
        if ($value === null || $value === '' || $value === '-') {
            return false;
        }
        if ($this->isPlaceholder($value)) {
            return false;
        }
        $cleaned = $this->cleanNumber($value);
        return is_numeric($cleaned);
    }

    /**
     * Clean number string
     */
    protected function cleanNumber($value): float
    {
        if (!is_string($value))
            return (float) $value;
        // Remove commas and spaces
        $cleaned = str_replace([',', ' ', '٫'], ['.', '', '.'], $value);
        return (float) $cleaned;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            // Minimal validation - we handle most logic in model()
        ];
    }

    /**
     * Batch size for inserts
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get count of updated products
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Get count of skipped rows
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Get import errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all failures
     */
    public function getImportFailures(): array
    {
        $failures = [];
        foreach ($this->failures() as $failure) {
            $failures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
        return $failures;
    }
}
