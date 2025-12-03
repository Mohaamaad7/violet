<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    /**
     * Get all wishlist items for the authenticated user
     */
    public function getWishlistItems(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return new Collection();
        }

        return Wishlist::where('user_id', $userId)
            ->with(['product' => fn($q) => $q->with('media')])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get wishlist count for authenticated user
     */
    public function getWishlistCount(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return 0;
        }

        return Wishlist::where('user_id', $userId)->count();
    }

    /**
     * Check if a product is in user's wishlist
     */
    public function isInWishlist(int $productId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add a product to wishlist
     */
    public function add(int $productId, ?int $userId = null): Wishlist
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            throw new \Exception('User must be authenticated to add to wishlist');
        }

        // Check if product exists
        $product = Product::findOrFail($productId);

        // Create or return existing
        return Wishlist::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove a product from wishlist
     */
    public function remove(int $productId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Toggle a product in wishlist (add if not exists, remove if exists)
     */
    public function toggle(int $productId, ?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return ['success' => false, 'action' => null, 'in_wishlist' => false];
        }

        if ($this->isInWishlist($productId, $userId)) {
            $this->remove($productId, $userId);
            return ['success' => true, 'action' => 'removed', 'in_wishlist' => false];
        } else {
            $this->add($productId, $userId);
            return ['success' => true, 'action' => 'added', 'in_wishlist' => true];
        }
    }

    /**
     * Clear all wishlist items for a user
     */
    public function clear(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return 0;
        }

        return Wishlist::where('user_id', $userId)->delete();
    }

    /**
     * Get product IDs in user's wishlist
     */
    public function getWishlistProductIds(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return [];
        }

        return Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
    }
}
