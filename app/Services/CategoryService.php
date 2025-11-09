<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Get all categories with optional filters
     */
    public function getAllCategories(array $filters = []): Collection
    {
        $query = Category::query();

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->with('parent', 'children')
                     ->withCount('products')
                     ->orderBy('order')
                     ->orderBy('name')
                     ->get();
    }

    /**
     * Get parent categories only
     */
    public function getParentCategories(): Collection
    {
        return Category::whereNull('parent_id')
            ->with('children')
            ->withCount('products')
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get category tree (hierarchical structure)
     */
    public function getCategoryTree(): Collection
    {
        return Category::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('order')->orderBy('name');
            }])
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find category by ID
     */
    public function findCategory(int $id): ?Category
    {
        return Category::with('parent', 'children', 'products')
            ->withCount('products')
            ->findOrFail($id);
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)
            ->with('parent', 'children', 'products')
            ->withCount('products')
            ->firstOrFail();
    }

    /**
     * Create new category
     */
    public function createCategory(array $data): Category
    {
        // Auto-generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        return Category::create($data);
    }

    /**
     * Update existing category
     */
    public function updateCategory(int $id, array $data): Category
    {
        $category = $this->findCategory($id);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $category->name) {
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $id);
        }

        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->findCategory($id);

        // Check if category has children
        if ($category->children()->count() > 0) {
            throw new \Exception('Cannot delete category with subcategories. Please delete or move subcategories first.');
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            throw new \Exception('Cannot delete category with products. Please delete or move products first.');
        }

        return $category->delete();
    }

    /**
     * Restore soft-deleted category
     */
    public function restoreCategory(int $id): Category
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return $category->fresh();
    }

    /**
     * Toggle category active status
     */
    public function toggleActive(int $id): Category
    {
        $category = $this->findCategory($id);
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh();
    }

    /**
     * Update category order
     */
    public function updateOrder(int $id, int $order): Category
    {
        $category = $this->findCategory($id);
        $category->update(['order' => $order]);
        return $category->fresh();
    }

    /**
     * Move category to different parent
     */
    public function moveCategory(int $id, ?int $newParentId): Category
    {
        $category = $this->findCategory($id);

        // Validate: Cannot move category to itself or its own children
        if ($newParentId === $id) {
            throw new \Exception('Cannot move category to itself.');
        }

        if ($newParentId && $this->isDescendant($id, $newParentId)) {
            throw new \Exception('Cannot move category to its own descendant.');
        }

        $category->update(['parent_id' => $newParentId]);
        return $category->fresh();
    }

    /**
     * Check if a category is descendant of another
     */
    protected function isDescendant(int $ancestorId, int $descendantId): bool
    {
        $descendant = Category::find($descendantId);

        while ($descendant && $descendant->parent_id) {
            if ($descendant->parent_id === $ancestorId) {
                return true;
            }
            $descendant = $descendant->parent;
        }

        return false;
    }

    /**
     * Ensure slug is unique
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Category::where('slug', $slug);
            
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
     * Get category statistics
     */
    public function getCategoryStats(int $id): array
    {
        $category = $this->findCategory($id);

        return [
            'total_products' => $category->products()->count(),
            'active_products' => $category->products()->where('is_active', true)->count(),
            'in_stock_products' => $category->products()->where('stock', '>', 0)->count(),
            'out_of_stock_products' => $category->products()->where('stock', '<=', 0)->count(),
            'featured_products' => $category->products()->where('is_featured', true)->count(),
            'subcategories' => $category->children()->count(),
        ];
    }
}
