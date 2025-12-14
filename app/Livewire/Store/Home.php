<?php

namespace App\Livewire\Store;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $featuredProducts = Product::with('category')
            ->where('is_featured', true)
            ->active()
            ->where('stock', '>', 0)
            ->take(8)
            ->get();

        $onSaleProducts = Product::with('category')
            ->whereNotNull('sale_price')
            ->active()
            ->where('stock', '>', 0)
            ->take(8)
            ->get();

        $newArrivals = Product::with('category')
            ->active()
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::whereNull('parent_id')
            ->where('is_active', true) // Categories table DOES have is_active, checking just in case
            ->withCount('products')
            ->orderBy('order')
            ->take(6)
            ->get();

        return view('livewire.store.home', [
            'featuredProducts' => $featuredProducts,
            'onSaleProducts' => $onSaleProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
        ])->layout('layouts.store');
    }
}
