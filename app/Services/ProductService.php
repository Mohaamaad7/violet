<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Get all products with filters and pagination
     */
    public function getAllProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with('category');

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by featured
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Filter by stock status
        if (isset($filters['stock_status'])) {
            match ($filters['stock_status']) {
                'in_stock' => $query->where('stock', '>', 0),
                'out_of_stock' => $query->where('stock', '<=', 0),
                'low_stock' => $query->whereBetween('stock', [1, 10]),
                default => null
            };
        }

        // Filter by price range
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Search
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return Product::with('category')
            ->featured()
            ->active()
            ->inStock()
            ->limit($limit)
            ->get();
    }

    /**
     * Get products on sale
     */
    public function getOnSaleProducts(int $limit = 10): Collection
    {
        return Product::with('category')
            ->whereNotNull('sale_price')
            ->active()
            ->inStock()
            ->limit($limit)
            ->get();
    }

    /**
     * Find product by ID
     */
    public function findProduct(int $id): ?Product
    {
        return Product::with(['category', 'images', 'variants', 'reviews'])
            ->findOrFail($id);
    }

    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->with(['category', 'images', 'variants', 'reviews'])
            ->firstOrFail();
    }

    /**
     * Create new product
     */
    public function createProduct(array $data): Product
    {
        // Auto-generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        // Auto-generate SKU if not provided
        if (!isset($data['sku']) || empty($data['sku'])) {
            $data['sku'] = $this->generateSKU();
        }

        return Product::create($data);
    }

    /**
     * Create product with images
     * 
     * @param array $data Product data including 'images' array
     * @return Product
     * @throws \Exception
     */
    public function createWithImages(array $data): Product
    {
        \DB::beginTransaction();
        
        try {
            // Extract images data if provided
            $images = $data['images'] ?? [];
            unset($data['images']);
            
            // Create product
            $product = $this->createProduct($data);
            
            // Add images if provided
            if (!empty($images)) {
                $this->syncImages($product, $images);
            }
            
            \DB::commit();
            return $product->fresh(['images', 'variants']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("Failed to create product with images: " . $e->getMessage());
        }
    }

    /**
     * Update product with images
     * 
     * @param Product $product
     * @param array $data Product data including optional 'images' array
     * @return Product
     * @throws \Exception
     */
    public function updateWithImages(Product $product, array $data): Product
    {
        \DB::beginTransaction();
        
        try {
            // Extract images data if provided
            $images = $data['images'] ?? null;
            unset($data['images']);
            
            // Update slug if name changed
            if (isset($data['name']) && $data['name'] !== $product->name) {
                if (!isset($data['slug']) || empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['name']);
                }
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], $product->id);
            }
            
            // Update product
            $product->update($data);
            
            // Update images if provided
            if ($images !== null) {
                $this->syncImages($product, $images);
            }
            
            \DB::commit();
            return $product->fresh(['images', 'variants']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("Failed to update product with images: " . $e->getMessage());
        }
    }

    /**
     * Sync product variants
     * 
     * @param Product $product
     * @param array $variants Array of variant data [['sku' => '...', 'name' => '...', ...], ...]
     * @return Product
     * @throws \Exception
     */
    public function syncVariants(Product $product, array $variants): Product
    {
        \DB::beginTransaction();
        
        try {
            // Delete existing variants
            $product->variants()->delete();
            
            // Create new variants
            foreach ($variants as $variantData) {
                // Validate required fields
                if (!isset($variantData['sku']) || !isset($variantData['name'])) {
                    throw new \Exception("Variant must have 'sku' and 'name' fields");
                }
                
                // Ensure variant SKU is unique
                if (\App\Models\ProductVariant::where('sku', $variantData['sku'])->exists()) {
                    throw new \Exception("Variant SKU '{$variantData['sku']}' already exists");
                }
                
                $product->variants()->create([
                    'sku' => $variantData['sku'],
                    'name' => $variantData['name'],
                    'price' => $variantData['price'] ?? $product->price,
                    'stock' => $variantData['stock'] ?? 0,
                    'attributes' => $variantData['attributes'] ?? [],
                ]);
            }
            
            \DB::commit();
            return $product->fresh(['variants']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception("Failed to sync variants: " . $e->getMessage());
        }
    }

    /**
     * Sync product images
     * 
     * @param Product $product
     * @param array $images Array of image data [['image' => '...', 'is_primary' => true, ...], ...]
     * @return void
     */
    protected function syncImages(Product $product, array $images): void
    {
        // Delete existing images
        $product->images()->delete();
        
        // Ensure only one primary image
        $hasPrimary = false;
        
        foreach ($images as $index => $imageData) {
            if (!isset($imageData['image']) && !isset($imageData['image_path'])) {
                continue;
            }
            
            // Set first image as primary if no primary specified
            $isPrimary = $imageData['is_primary'] ?? ($index === 0 && !$hasPrimary);
            
            if ($isPrimary && $hasPrimary) {
                $isPrimary = false; // Only allow one primary
            }
            
            if ($isPrimary) {
                $hasPrimary = true;
            }
            
            $product->images()->create([
                'image_path' => $imageData['image'] ?? $imageData['image_path'],
                'is_primary' => $isPrimary,
                'order' => $imageData['order'] ?? $index,
            ]);
        }
    }

    /**
     * Update existing product
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->findProduct($id);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $id);
        }

        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->findProduct($id);

        // Check if product has orders
        $ordersCount = \DB::table('order_items')
            ->where('product_id', $id)
            ->count();

        if ($ordersCount > 0) {
            throw new \Exception('Cannot delete product that has been ordered. Consider deactivating it instead.');
        }

        // Delete related data
        $product->images()->delete();
        $product->variants()->delete();
        $product->reviews()->delete();

        return $product->delete();
    }

    /**
     * Restore soft-deleted product
     */
    public function restoreProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return $product->fresh();
    }

    /**
     * Toggle product active status
     */
    public function toggleActive(int $id): Product
    {
        $product = $this->findProduct($id);
        $product->update(['is_active' => !$product->is_active]);
        return $product->fresh();
    }

    /**
     * Toggle product featured status
     */
    public function toggleFeatured(int $id): Product
    {
        $product = $this->findProduct($id);
        $product->update(['is_featured' => !$product->is_featured]);
        return $product->fresh();
    }

    /**
     * Update product stock
     */
    public function updateStock(int $id, int $quantity): Product
    {
        $product = $this->findProduct($id);
        $product->update(['stock' => $quantity]);
        return $product->fresh();
    }

    /**
     * Decrease product stock (when order placed)
     */
    public function decreaseStock(int $id, int $quantity): Product
    {
        $product = $this->findProduct($id);

        if ($product->stock < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$product->stock}, Requested: {$quantity}");
        }

        $product->decrement('stock', $quantity);
        return $product->fresh();
    }

    /**
     * Increase product stock (when order cancelled/returned)
     */
    public function increaseStock(int $id, int $quantity): Product
    {
        $product = $this->findProduct($id);
        $product->increment('stock', $quantity);
        return $product->fresh();
    }

    /**
     * Update product price
     */
    public function updatePrice(int $id, float $price, ?float $salePrice = null): Product
    {
        $product = $this->findProduct($id);
        
        $updateData = ['price' => $price];
        if ($salePrice !== null) {
            $updateData['sale_price'] = $salePrice;
        }

        $product->update($updateData);
        return $product->fresh();
    }

    /**
     * Generate unique SKU
     */
    protected function generateSKU(): string
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Ensure slug is unique
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get product statistics
     */
    public function getProductStats(int $id): array
    {
        $product = $this->findProduct($id);

        return [
            'views' => \DB::table('product_views')
                ->where('product_id', $id)
                ->count(),
            'orders' => \DB::table('order_items')
                ->where('product_id', $id)
                ->count(),
            'total_sold' => \DB::table('order_items')
                ->where('product_id', $id)
                ->sum('quantity'),
            'reviews_count' => $product->reviews()->count(),
            'average_rating' => $product->reviews()->avg('rating'),
            'wishlist_count' => \DB::table('wishlists')
                ->where('product_id', $id)
                ->count(),
        ];
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::with('category')
            ->whereBetween('stock', [1, $threshold])
            ->active()
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::with('category')
            ->where('stock', '<=', 0)
            ->active()
            ->get();
    }

    /**
     * Add stock to product (with optional batch)
     */
    public function addStock(int $productId, int $quantity, ?string $notes = null, ?array $batchData = null): Product
    {
        $stockMovementService = app(StockMovementService::class);
        
        $product = Product::findOrFail($productId);
        
        $batchId = null;
        
        // Create batch if batch data provided
        if ($batchData) {
            $batchService = app(BatchService::class);
            $batch = $batchService->createBatch(array_merge($batchData, [
                'product_id' => $productId,
                'quantity_initial' => $quantity,
                'quantity_current' => $quantity,
            ]));
            $batchId = $batch->id;
        } else {
            // Record stock movement without batch
            $stockMovementService->addStock(
                $productId,
                $quantity,
                'restock',
                null,
                $notes,
                null
            );
        }
        
        return $product->fresh();
    }

    /**
     * Deduct stock from product
     */
    public function deductStock(int $productId, int $quantity, $reference = null): Product
    {
        $stockMovementService = app(StockMovementService::class);
        
        $product = Product::findOrFail($productId);
        
        if ($product->stock < $quantity) {
            throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Required: {$quantity}");
        }
        
        $stockMovementService->deductStock(
            $productId,
            $quantity,
            $reference,
            null,
            null
        );
        
        return $product->fresh();
    }

    /**
     * Check stock availability
     */
    public function checkStockAvailability(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return false;
        }
        
        return $product->stock >= $quantity;
    }

    /**
     * Get stock status for product
     */
    public function getStockStatus(int $productId): array
    {
        $product = Product::with(['batches' => function ($query) {
            $query->where('status', 'active')->orderBy('expiry_date');
        }])->findOrFail($productId);
        
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'current_stock' => $product->stock,
            'low_stock_threshold' => $product->low_stock_threshold,
            'is_low_stock' => $product->stock <= $product->low_stock_threshold,
            'is_out_of_stock' => $product->stock <= 0,
            'batches_count' => $product->batches->count(),
            'batches' => $product->batches->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'quantity' => $batch->quantity_current,
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'days_until_expiry' => $batch->days_until_expiry,
                    'alert_level' => $batch->alert_level,
                ];
            }),
        ];
    }
}
