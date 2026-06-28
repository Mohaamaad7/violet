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

        // 1. Combo Rules
        $combos = \App\Models\ComboRule::active()->ordered()->get()->map(function ($combo) {
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

        // 2. Discount Codes
        $discounts = \App\Models\DiscountCode::valid()->whereNull('influencer_id')->get()->map(function ($discount) {
            return [
                'id' => 'discount_' . $discount->id,
                'type' => 'discount',
                'title' => $discount->code,
                'description' => $discount->description ?? 'كود خصم عام',
                'image' => 'https://placehold.co/400x400/fdf4ff/c026d3?text=' . urlencode($discount->code),
                'original_price' => null,
                'offer_price' => $discount->type === 'fixed' ? $discount->value : null,
                'discount_percentage' => $discount->type === 'percentage' ? $discount->value : null,
                'currency' => 'EGP',
                'is_active' => true,
                'valid_until' => $discount->expires_at,
            ];
        });
        $offers = $offers->merge($discounts);

        // 3. Products on Sale
        $saleProducts = Product::with('category')->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->active()->take(8)->get()->map(function ($product) {
            return [
                'id' => 'bundle_' . $product->id,
                'type' => 'bundle',
                'title' => $product->name,
                'description' => 'خصم مباشر على المنتج',
                'image' => $product->primary_image_url ?? 'https://placehold.co/400x400/f3f4f6/9ca3af?text=' . urlencode($product->name),
                'original_price' => $product->price,
                'offer_price' => $product->sale_price,
                'discount_percentage' => round((($product->price - $product->sale_price) / $product->price) * 100),
                'currency' => 'EGP',
                'is_active' => true,
                'valid_until' => null,
            ];
        });
        $offers = $offers->merge($saleProducts);

        return view('livewire.store.home', [
            'featuredProducts' => $featuredProducts,
            'onSaleProducts' => $onSaleProducts,
            'newArrivals' => $newArrivals,
            'categories' => $categories,
            'unifiedOffers' => $offers,
        ])->layout('layouts.store');
    }
}
