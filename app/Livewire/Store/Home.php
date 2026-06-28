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

        $offers = collect();

        $offers = collect();

        // 1. Combo Rules (Only those marked for homepage)
        $combos = \App\Models\ComboRule::active()->where('show_on_homepage', true)->ordered()->get()->map(function ($combo) {
            return [
                'id' => 'combo_' . $combo->id,
                'type' => 'combo',
                'title' => $combo->name,
                'description' => $combo->description ?? 'عرض كومبو مميز',
                'image' => $combo->image_path ? asset('storage/' . $combo->image_path) : 'https://placehold.co/400x400/e9d5ff/6b21a8?text=' . urlencode($combo->name),
                'original_price' => null,
                'offer_price' => $combo->discount_type === 'fixed' ? $combo->fixed_price : null,
                'discount_percentage' => $combo->discount_type === 'percentage' ? $combo->discount_percentage : null,
                'currency' => 'EGP',
                'is_active' => $combo->is_active,
                'valid_until' => $combo->ends_at,
            ];
        });
        $offers = $offers->merge($combos);

        return view('livewire.store.home', [
            'featuredProducts' => $featuredProducts,
            'onSaleProducts' => $onSaleProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
            'unifiedOffers' => $offers,
        ])->layout('layouts.store');
    }
}
