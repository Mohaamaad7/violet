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
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->take(8)
            ->get();
            
        $onSaleProducts = Product::with('category')
            ->whereNotNull('sale_price')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->take(8)
            ->get();
            
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('order')
            ->take(6)
            ->get();
        
        return view('livewire.store.home', [
            'featuredProducts' => $featuredProducts,
            'onSaleProducts' => $onSaleProducts,
            'categories' => $categories,
        ])->layout('layouts.store');
    }
}
