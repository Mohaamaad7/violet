<?php

namespace App\Livewire\Store;

use App\Models\Category;
use App\Models\Product;
use App\Services\StorefrontLayout;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    // Filters with URL binding
    #[Url(except: [])]
    public array $selectedCategories = [];

    #[Url(except: '')]
    public string $minPrice = '';

    #[Url(except: '')]
    public string $maxPrice = '';

    #[Url(except: null)]
    public ?int $selectedRating = null;

    #[Url(except: [])]
    public array $selectedBrands = [];

    #[Url(except: false)]
    public bool $onSaleOnly = false;

    #[Url(except: 'all')]
    public string $stockStatus = 'all'; // all, in_stock, out_of_stock

    #[Url(except: 'default')]
    public string $sortBy = 'default';

    // Search
    #[Url(except: '')]
    public string $search = '';

    // Pagination
    public int $perPage = 12;

    // Incoming Category Slug from URL
    #[Url]
    public ?string $category = null;

    public function mount()
    {
        if ($this->categoryFirst && $this->category) {
            abort_unless($this->categoryRoot, 404);
        }

        if ($this->categoryFirst && $this->categoryRoot) {
            $root = $this->categoryRoot;
            $allowedIds = [$root->id, ...$root->getDescendantIds()];
            $incoming = $this->storefrontCategories->firstWhere('slug', $this->category);
            $selected = array_values(array_intersect(array_map('intval', $this->selectedCategories), $allowedIds));
            $this->selectedCategories = $selected ?: [$incoming->id];
            $this->category = $root->slug;

            return;
        }

        // If 'category' slug is provided in URL, pre-select it
        if ($this->category) {
            $categoryModel = Category::where('slug', $this->category)->first();
            if ($categoryModel) {
                if (! in_array($categoryModel->id, $this->selectedCategories)) {
                    $this->selectedCategories[] = $categoryModel->id;
                }
            }
        }
    }

    #[Computed]
    public function categoryFirst(): bool
    {
        return app(StorefrontLayout::class)->isCategoryFirst();
    }

    #[Computed]
    public function storefrontCategories(): Collection
    {
        return $this->categoryFirst ? app(StorefrontLayout::class)->categoryTree(withProductCounts: true) : new Collection;
    }

    #[Computed]
    public function categoryRoot(): ?Category
    {
        $category = $this->storefrontCategories->firstWhere('slug', $this->category);
        $visited = [];

        while ($category && $category->parent_id) {
            if (in_array($category->id, $visited, true)) {
                return null;
            }
            $visited[] = $category->id;
            $category = $this->storefrontCategories->find($category->parent_id);
        }

        return $category;
    }

    /** Each entry supplies the children for one horizontal strap. */
    #[Computed]
    public function strapPath(): array
    {
        $root = $this->categoryRoot;
        if (! $root) {
            return [];
        }

        $selected = count($this->selectedCategories) === 1
            ? $this->storefrontCategories->find((int) $this->selectedCategories[0])
            : null;
        $path = [];
        $visited = [];

        while ($selected && $selected->id !== $root->id) {
            if (in_array($selected->id, $visited, true)) {
                return [$root];
            }
            $visited[] = $selected->id;
            array_unshift($path, $selected);
            $selected = $this->storefrontCategories->find($selected->parent_id);
        }

        return $selected ? [$root, ...$path] : [$root];
    }

    public function selectStrapCategory(int $categoryId): void
    {
        $root = $this->categoryRoot;
        abort_unless($this->categoryFirst && $root
            && in_array($categoryId, [$root->id, ...$root->getDescendantIds()], true), 404);

        // Replace the parent filter: combining parent + child would include siblings.
        $this->selectedCategories = [$categoryId];
        unset($this->strapPath, $this->products);
        $this->resetPage();
    }

    /**
     * Get available brands from products
     */
    #[Computed]
    public function availableBrands(): array
    {
        return Product::where('status', 'active')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->toArray();
    }

    /**
     * Get price range from products
     */
    #[Computed]
    public function priceRange(): array
    {
        $stats = Product::where('status', 'active')
            ->selectRaw('MIN(COALESCE(sale_price, price)) as min_price, MAX(COALESCE(sale_price, price)) as max_price')
            ->first();

        return [
            'min' => (int) ($stats->min_price ?? 0),
            'max' => (int) ($stats->max_price ?? 10000),
        ];
    }

    /**
     * Check if any filters are active
     */
    #[Computed]
    public function hasActiveFilters(): bool
    {
        return ! empty($this->selectedCategories)
            || $this->minPrice !== ''
            || $this->maxPrice !== ''
            || $this->selectedRating !== null
            || ! empty($this->selectedBrands)
            || $this->onSaleOnly
            || $this->stockStatus !== 'all'
            || $this->search !== '';
    }

    /**
     * Get count of active filters
     */
    #[Computed]
    public function activeFiltersCount(): int
    {
        $count = 0;
        $count += count($this->selectedCategories);
        $count += ($this->minPrice !== '' || $this->maxPrice !== '') ? 1 : 0;
        $count += $this->selectedRating !== null ? 1 : 0;
        $count += count($this->selectedBrands);
        $count += $this->onSaleOnly ? 1 : 0;
        $count += $this->stockStatus !== 'all' ? 1 : 0;
        $count += $this->search !== '' ? 1 : 0;

        return $count;
    }

    /**
     * Reset pagination when filters change
     */
    public function updatedSelectedCategories($value): void
    {
        // Convert to integers and remove empty values
        $this->selectedCategories = array_values(
            array_filter(
                array_map('intval', (array) $value),
                fn ($v) => $v > 0
            )
        );
        $this->resetPage();
    }

    /**
     * Toggle category selection
     */
    public function toggleCategory(int $categoryId): void
    {
        $key = array_search($categoryId, $this->selectedCategories);

        if ($key !== false) {
            // Remove category
            unset($this->selectedCategories[$key]);
            $this->selectedCategories = array_values($this->selectedCategories);
        } else {
            // Add category
            $this->selectedCategories[] = $categoryId;
        }

        $this->resetPage();
    }

    /**
     * Toggle brand selection
     */
    public function toggleBrand(string $brand): void
    {
        $key = array_search($brand, $this->selectedBrands);

        if ($key !== false) {
            // Remove brand
            unset($this->selectedBrands[$key]);
            $this->selectedBrands = array_values($this->selectedBrands);
        } else {
            // Add brand
            $this->selectedBrands[] = $brand;
        }

        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedRating(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBrands($value): void
    {
        // Remove null/empty values
        $this->selectedBrands = array_values(
            array_filter((array) $value, fn ($v) => $v !== null && $v !== '')
        );
        $this->resetPage();
    }

    public function updatedOnSaleOnly(): void
    {
        $this->resetPage();
    }

    public function updatedStockStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Remove a single category from filter
     */
    public function removeCategory(int $categoryId): void
    {
        $this->selectedCategories = array_values(
            array_filter($this->selectedCategories, fn ($id) => $id != $categoryId)
        );
        $this->resetPage();
    }

    /**
     * Remove a single brand from filter
     */
    public function removeBrand(string $brand): void
    {
        $this->selectedBrands = array_values(
            array_filter($this->selectedBrands, fn ($b) => $b !== $brand)
        );
        $this->resetPage();
    }

    /**
     * Clear price filter
     */
    public function clearPriceFilter(): void
    {
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
    }

    /**
     * Clear rating filter
     */
    public function clearRating(): void
    {
        $this->selectedRating = null;
        $this->resetPage();
    }

    /**
     * Clear search
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $root = $this->categoryFirst ? $this->categoryRoot : null;
        $this->selectedCategories = $root ? [$root->id] : [];
        $this->category = $root?->slug;
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->selectedRating = null;
        $this->selectedBrands = [];
        $this->onSaleOnly = false;
        $this->stockStatus = 'all';
        $this->search = '';
        $this->sortBy = 'default';
        $this->resetPage();
    }

    /**
     * Get filtered and sorted products
     */
    #[Computed]
    public function products()
    {
        $query = Product::with(['category', 'media'])
            ->where('status', 'active');

        if ($this->categoryRoot) {
            $query->whereIn('category_id', [$this->categoryRoot->id, ...$this->categoryRoot->getDescendantIds()]);
        }

        // Search filter
        if ($this->search !== '') {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where(fn ($q) => $q->searchCatalog($searchTerm))
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhere('brand', 'like', $searchTerm);
            });
        }

        // Filter by categories (including children categories)
        if (! empty($this->selectedCategories)) {
            $categoryIds = $this->categoryFirst ? [] : $this->selectedCategories;

            foreach ($this->selectedCategories as $categoryId) {
                $category = $this->categoryFirst
                    ? $this->storefrontCategories->find((int) $categoryId)
                    : Category::find($categoryId);
                if ($category && method_exists($category, 'getDescendantIds')) {
                    $categoryIds = array_merge($categoryIds, [$category->id], $category->getDescendantIds());
                }
            }

            $query->whereIn('category_id', array_unique($categoryIds));
        }

        // Filter by price range
        $minPrice = is_numeric($this->minPrice) && $this->minPrice !== '' ? (float) $this->minPrice : null;
        $maxPrice = is_numeric($this->maxPrice) && $this->maxPrice !== '' ? (float) $this->maxPrice : null;

        if ($minPrice !== null || $maxPrice !== null) {
            $query->where(function ($q) use ($minPrice, $maxPrice) {
                $q->where(function ($sq) use ($minPrice, $maxPrice) {
                    // Check sale_price if exists
                    $sq->whereNotNull('sale_price');
                    if ($minPrice !== null) {
                        $sq->where('sale_price', '>=', $minPrice);
                    }
                    if ($maxPrice !== null) {
                        $sq->where('sale_price', '<=', $maxPrice);
                    }
                })->orWhere(function ($sq) use ($minPrice, $maxPrice) {
                    // Check regular price if no sale_price
                    $sq->whereNull('sale_price');
                    if ($minPrice !== null) {
                        $sq->where('price', '>=', $minPrice);
                    }
                    if ($maxPrice !== null) {
                        $sq->where('price', '<=', $maxPrice);
                    }
                });
            });
        }

        // Filter by rating
        if ($this->selectedRating !== null) {
            $query->where('average_rating', '>=', $this->selectedRating);
        }

        // Filter by brands
        if (! empty($this->selectedBrands)) {
            $query->whereIn('brand', $this->selectedBrands);
        }

        // Filter by on sale
        if ($this->onSaleOnly) {
            $query->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price');
        }

        // Filter by stock status
        if ($this->stockStatus === 'in_stock') {
            $query->where('stock', '>', 0);
        } elseif ($this->stockStatus === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'newest':
                $query->latest('created_at');
                break;
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'rating_desc':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('sales_count', 'desc');
                break;
            default:
                // Featured first, then by sales count (popularity), then newest
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('sales_count', 'desc')
                    ->latest('created_at');
                break;
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.store.product-list', [
            'products' => $this->products,
            'categories' => $this->categoryFirst
                ? $this->storefrontCategories->whereNull('parent_id')->values()
                : Category::with('children')
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get(),
        ]);
    }
}
