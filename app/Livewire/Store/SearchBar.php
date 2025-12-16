<?php

namespace App\Livewire\Store;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Url;

class SearchBar extends Component
{
    #[Url(as: 'q', keep: true)]
    public $search = '';

    public $results = [];
    public $showResults = false;
    public $selectedIndex = -1;
    public $isMobile = false;

    protected $queryString = ['search' => ['as' => 'q', 'except' => '']];

    public function updatedSearch()
    {
        $this->selectedIndex = -1;

        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showResults = false;
            return;
        }

        $this->performSearch();
    }

    public function performSearch()
    {
        $searchTerm = '%' . $this->search . '%';

        $this->results = Product::query()
            ->where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            })
            ->with(['media', 'category'])
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->final_price,
                    'original_price' => $product->price,
                    'is_on_sale' => $product->is_on_sale,
                    'image' => $product->getFirstMediaUrl('product-images', 'thumbnail') ?: asset('images/default-product.svg'),
                    'category' => $product->category?->name ?? '',
                    'rating' => $product->average_rating ?? 0,
                    'in_stock' => $product->stock > 0,
                ];
            })
            ->toArray();

        $this->showResults = count($this->results) > 0 || strlen($this->search) >= 2;
    }

    public function selectResult($index)
    {
        if (isset($this->results[$index])) {
            return redirect()->route('product.show', $this->results[$index]['slug']);
        }
    }

    public function viewAllResults()
    {
        return redirect()->route('products.index', ['search' => $this->search]);
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->results = [];
        $this->showResults = false;
        $this->selectedIndex = -1;
    }

    public function closeResults()
    {
        $this->showResults = false;
    }

    // Keyboard navigation
    public function incrementIndex()
    {
        if ($this->selectedIndex < count($this->results) - 1) {
            $this->selectedIndex++;
        }
    }

    public function decrementIndex()
    {
        if ($this->selectedIndex > -1) {
            $this->selectedIndex--;
        }
    }

    public function selectCurrent()
    {
        if ($this->selectedIndex >= 0 && isset($this->results[$this->selectedIndex])) {
            return redirect()->route('product.show', $this->results[$this->selectedIndex]['slug']);
        } else {
            return $this->viewAllResults();
        }
    }

    public function render()
    {
        return view('livewire.store.search-bar');
    }
}
