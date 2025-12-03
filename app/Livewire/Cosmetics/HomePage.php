<?php

namespace App\Livewire\Cosmetics;

use App\Models\Product;
use Livewire\Component;

/**
 * Cosmetics Theme Home Page Component
 * 
 * Displays the dark-themed cosmetics landing page with:
 * - Hero section with featured product
 * - Feature strip (4 selling points)
 * - Best Sellers grid (uses is_featured products)
 * - Newsletter subscription banner
 * 
 * @see resources/views/livewire/cosmetics/home-page.blade.php
 */
class HomePage extends Component
{
    /**
     * Get the featured product for the hero section.
     * Returns the first featured product with images.
     */
    public function getFeaturedProductProperty(): ?Product
    {
        return Product::with(['category', 'media'])
            ->where('is_featured', true)
            ->where('status', 'active')
            ->whereHas('media')
            ->first();
    }

    /**
     * Get best seller products.
     * 
     * TODO: Implement actual best_sellers scope based on order_items count
     * For now, using is_featured products as placeholder
     * 
     * @return \Illuminate\Database\Eloquent\Collection<Product>
     */
    public function getBestSellersProperty()
    {
        return Product::with(['category', 'media'])
            ->where('is_featured', true)
            ->where('status', 'active')
            ->take(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.cosmetics.home-page', [
            'featuredProduct' => $this->featuredProduct,
            'bestSellers' => $this->bestSellers,
        ])->layout('layouts.cosmetics', [
            'title' => __('messages.cosmetics.page_title'),
        ]);
    }
}
