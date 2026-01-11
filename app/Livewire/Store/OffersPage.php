<?php

namespace App\Livewire\Store;

use App\Models\DiscountCode;
use App\Models\Product;
use Livewire\Component;

class OffersPage extends Component
{
    public function render()
    {
        // Get public active discount codes (not linked to influencers)
        $discountCodes = DiscountCode::valid()
            ->whereNull('influencer_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get products on sale
        $saleProducts = Product::with('category')
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->active()
            ->where('stock', '>', 0)
            ->orderByRaw('((price - sale_price) / price) DESC') // Order by discount percentage
            ->take(16)
            ->get();

        return view('livewire.store.offers-page', [
            'discountCodes' => $discountCodes,
            'saleProducts' => $saleProducts,
        ])->layout('layouts.store', [
                    'title' => __('messages.offers_page_title'),
                    'description' => __('messages.offers_page_description'),
                ]);
    }
}
