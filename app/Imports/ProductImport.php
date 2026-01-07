<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductImport
{
    protected string $mode; // 'create' or 'update'
    protected array $validRows;
    protected int $processedCount = 0;
    protected int $errorCount = 0;
    protected array $errors = [];

    /**
     * @param string $mode 'create' or 'update'
     * @param array $validRows Pre-validated rows from ProductImportValidator
     */
    public function __construct(string $mode, array $validRows)
    {
        $this->mode = $mode;
        $this->validRows = $validRows;
    }

    /**
     * Execute the import
     */
    public function execute(): array
    {
        foreach ($this->validRows as $item) {
            try {
                if ($this->mode === 'create') {
                    $this->createProduct($item['data']);
                } else {
                    $this->updateProduct($item['data']);
                }
                $this->processedCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $item['row'],
                    'message' => $e->getMessage(),
                ];
                Log::error("Product import error", [
                    'row' => $item['row'],
                    'error' => $e->getMessage(),
                    'data' => $item['data'],
                ]);
            }
        }

        return [
            'success' => $this->errorCount === 0,
            'processed' => $this->processedCount,
            'errors' => $this->errorCount,
            'errorDetails' => $this->errors,
        ];
    }

    /**
     * Create a new product
     */
    protected function createProduct(array $data): void
    {
        $product = new Product();

        $product->name = $data['name'];
        $product->slug = Str::slug($data['name']) . '-' . Str::random(5);
        $product->sku = $data['sku'] ?? $this->generateSku();
        $product->category_id = $data['category_id'];
        $product->price = $data['price'];
        $product->sale_price = $data['sale_price'];
        $product->cost_price = $data['cost_price'];
        $product->stock = $data['stock'];
        $product->low_stock_threshold = $data['low_stock_threshold'] ?? 10;
        $product->status = $data['status'] ?? 'draft';
        $product->description = $data['description'] ?? null;

        $product->save();

        Log::info("Product created via import", ['id' => $product->id, 'name' => $product->name]);
    }

    /**
     * Update an existing product
     */
    protected function updateProduct(array $data): void
    {
        $product = Product::findOrFail($data['id']);

        $updates = [];

        // Only update non-null values
        if (!empty($data['name'])) {
            $updates['name'] = $data['name'];
        }

        if ($data['price'] !== null) {
            $updates['price'] = $data['price'];
        }

        // Allow setting sale_price to null (removing discount)
        if (array_key_exists('sale_price', $data)) {
            $updates['sale_price'] = $data['sale_price'];
        }

        if ($data['cost_price'] !== null) {
            $updates['cost_price'] = $data['cost_price'];
        }

        if (!empty($data['status'])) {
            $updates['status'] = $data['status'];
        }

        // NOTE: We intentionally do NOT update stock here
        // Stock should be managed through stock movements

        if (!empty($updates)) {
            $product->update($updates);
            Log::info("Product updated via import", ['id' => $product->id, 'updates' => $updates]);
        }
    }

    /**
     * Generate a unique SKU
     */
    protected function generateSku(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Get processed count
     */
    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
