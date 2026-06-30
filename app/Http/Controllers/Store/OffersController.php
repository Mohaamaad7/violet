<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ComboRule;
use Illuminate\Http\Request;

class OffersController extends Controller
{
    public function index()
    {
        // Map Combo Rules to the unified schema expected by the frontend
        $combos = ComboRule::active()->ordered()->get()->map(function ($combo) {
            return [
                'id' => 'combo_' . $combo->id,
                'type' => 'combo',
                'title' => $combo->name,
                'description' => $combo->description,
                'image' => $combo->image_path ? asset('storage/' . $combo->image_path) : 'https://placehold.co/400x400/e9d5ff/6b21a8?text=' . urlencode($combo->name),
                'original_price' => null, // Calculate if possible or leave null
                'offer_price' => $combo->discount_type === 'fixed' ? $combo->fixed_price : null,
                'discount_percentage' => $combo->discount_type === 'percentage' ? $combo->discount_percentage : null,
                'currency' => 'EGP',
                'is_active' => $combo->is_active,
                'valid_until' => $combo->ends_at,
            ];
        });

        // If there are other models like bundles or discounts, map them similarly here and merge.
        // For now, we return the combos mapped to the unified schema.
        $offers = $combos; // e.g., $combos->merge($discounts)

        return view('store.offers.index', compact('offers'));
    }
}
