<?php

namespace App\Livewire\Store;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class ProductList extends Component
{
    use WithPagination;

    // Filters
    public $selectedCategories = [];
    public $minPrice = '';
    public $maxPrice = '';
    public $selectedRating = null;
    
    // Sorting
    public $sortBy = 'default'; // default, newest, price_asc, price_desc, rating_desc
    
    // Pagination
    public $perPage = 12;

    // Query Strings for URL parameters
    protected $queryString = [
        'selectedCategories' => ['except' => []],
        'minPrice' => ['except' => ''],
        'maxPrice' => ['except' => ''],
        'selectedRating' => ['except' => null],
        'sortBy' => ['except' => 'default'],
    ];

    /**
     * Reset pagination when filters change
     */
    public function updatingSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatingMinPrice($value)
    {
        // Allow empty string or null to clear the input - don't interfere
        if ($value === '' || $value === null) {
            // Let Livewire handle it naturally
            return;
        }
        
        if (is_numeric($value)) {
            $this->minPrice = (int)$value;
        }
        $this->resetPage();
    }

    public function updatingMaxPrice($value)
    {
        // Allow empty string or null to clear the input - don't interfere
        if ($value === '' || $value === null) {
            // Let Livewire handle it naturally
            return;
        }
        
        if (is_numeric($value)) {
            $this->maxPrice = (int)$value;
        }
        $this->resetPage();
    }

    public function updatingSelectedRating()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    /**
     * Remove a single category from filter
     */
    public function removeCategory($categoryId)
    {
        // Remove the category ID from the array
        $this->selectedCategories = array_values(
            array_filter($this->selectedCategories, fn($id) => $id != $categoryId)
        );
        
        $this->resetPage();
        
        // Use JavaScript to manually uncheck the checkbox
        $this->js(<<<JS
            document.querySelectorAll('input[type="checkbox"][value="{$categoryId}"]').forEach(el => {
                el.checked = false;
            });
        JS);
    }

    /**
     * Clear price filter
     */
    public function clearPriceFilter()
    {
        // Reset to empty strings
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
        
        // Use JavaScript to manually clear the inputs
        $this->js(<<<'JS'
            document.querySelectorAll('input[wire\\:model\\.live\\.debounce\\.500ms="minPrice"], input[wire\\:model\\.live\\.debounce\\.500ms="maxPrice"]').forEach(el => {
                el.value = '';
            });
        JS);
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        // Clear all filter properties
        $this->selectedCategories = [];
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->selectedRating = null;
        $this->sortBy = 'default';
        $this->resetPage();
        
        // Use JavaScript to manually clear all checkboxes and inputs
        $this->js(<<<'JS'
            // Uncheck all category checkboxes
            document.querySelectorAll('input[type="checkbox"][wire\\:model\\.live="selectedCategories"]').forEach(el => {
                el.checked = false;
            });
            
            // Clear price inputs
            document.querySelectorAll('input[wire\\:model\\.live\\.debounce\\.500ms="minPrice"], input[wire\\:model\\.live\\.debounce\\.500ms="maxPrice"]').forEach(el => {
                el.value = '';
            });
        JS);
    }

    /**
     * Get filtered and sorted products
     */
    #[Computed]
    public function products()
    {
        // Ensure price values are valid integers (handle empty strings/null)
        $minPrice = is_numeric($this->minPrice) && $this->minPrice !== '' ? (int)$this->minPrice : 0;
        $maxPrice = is_numeric($this->maxPrice) && $this->maxPrice !== '' ? (int)$this->maxPrice : 10000;
        
        // Ensure min is not greater than max
        if ($minPrice > $maxPrice) {
            $minPrice = 0;
            $maxPrice = 10000;
        }
        
        $query = Product::with(['category', 'images'])
            ->where('status', 'active');

        // Filter by categories (including children categories)
        if (!empty($this->selectedCategories)) {
            $categoryIds = $this->selectedCategories;
            
            // Get all descendant category IDs for selected categories
            foreach ($this->selectedCategories as $categoryId) {
                $category = \App\Models\Category::find($categoryId);
                if ($category) {
                    $categoryIds = array_merge($categoryIds, $category->getDescendantIds());
                }
            }
            
            $query->whereIn('category_id', array_unique($categoryIds));
        }

        // Filter by price range (using sanitized values)
        $query->where(function ($q) use ($minPrice, $maxPrice) {
            $q->whereBetween('price', [$minPrice, $maxPrice])
              ->orWhereBetween('sale_price', [$minPrice, $maxPrice])
              ->orWhere(function ($sq) use ($minPrice, $maxPrice) {
                  $sq->whereNotNull('sale_price')
                     ->whereRaw('sale_price >= ?', [$minPrice])
                     ->whereRaw('sale_price <= ?', [$maxPrice]);
              });
        });

        // Filter by rating
        if ($this->selectedRating) {
            $query->where('average_rating', '>=', $this->selectedRating);
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
            default:
                // Default sorting: Featured first, then newest
                $query->orderBy('is_featured', 'desc')
                      ->latest('created_at');
                break;
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.store.product-list', [
            'products' => $this->products,
        ]);
    }
}
