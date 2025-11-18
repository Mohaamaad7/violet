<?php

namespace App\Livewire\Store;

use App\Models\Product;
use Livewire\Component;

class FeaturedProducts extends Component
{
    public function render()
    {
        $products = Product::with(['category', 'images'])
            ->where('is_featured', true)
            ->where('status', 'active')
            ->take(8)
            ->get();
        
        return view('livewire.store.featured-products', [
            'products' => $products,
        ]);
    }
}
