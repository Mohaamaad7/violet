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
}
