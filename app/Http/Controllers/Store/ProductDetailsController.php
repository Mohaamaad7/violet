<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductDetailsController extends Controller
{
    /**
     * Display the product details page
     */
    public function show(string $slug)
    {
        // Find product by slug with all necessary relationships
        $product = Product::with([
            'category',
            'images' => fn($q) => $q->orderBy('order'),
            'variants' => fn($q) => $q->inStock(),
            'reviews' => fn($q) => $q->approved()->with('user')->latest()->take(10)
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        // Increment views count
        $product->increment('views_count');

        return view('store.product-details', compact('product'));
    }
}
