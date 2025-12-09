<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * WishlistService - Handles wishlist operations for customers
 * 
 * NOTE: This service is for Customers only, not admin Users.
 */
class WishlistService
{
    /**
     * Get the currently authenticated customer (if using customer guard)
     */
    private function getAuthenticatedCustomer(): ?Customer
    {
        // Check customer guard first
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }

        return null;
    }

    /**
     * Get customer ID - either from parameter or authenticated customer
     */
    private function getCustomerId(?int $customerId = null): ?int
    {
        if ($customerId) {
            return $customerId;
        }

        $customer = $this->getAuthenticatedCustomer();
        return $customer?->id;
    }

    /**
     * Get all wishlist items for the authenticated customer
     */
    public function getWishlistItems(?int $customerId = null): Collection
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return new Collection();
        }

        return Wishlist::where('customer_id', $customerId)
            ->with(['product' => fn($q) => $q->with('media')])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get wishlist count for authenticated customer
     */
    public function getWishlistCount(?int $customerId = null): int
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return 0;
        }

        return Wishlist::where('customer_id', $customerId)->count();
    }

    /**
     * Check if a product is in customer's wishlist
     */
    public function isInWishlist(int $productId, ?int $customerId = null): bool
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return false;
        }

        return Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Add a product to wishlist
     */
    public function add(int $productId, ?int $customerId = null): Wishlist
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            throw new \Exception('Customer must be authenticated to add to wishlist');
        }

        // Check if product exists
        $product = Product::findOrFail($productId);

        // Create or return existing
        return Wishlist::firstOrCreate([
            'customer_id' => $customerId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove a product from wishlist
     */
    public function remove(int $productId, ?int $customerId = null): bool
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return false;
        }

        return Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Toggle a product in wishlist (add if not exists, remove if exists)
     */
    public function toggle(int $productId, ?int $customerId = null): array
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return ['success' => false, 'action' => null, 'in_wishlist' => false];
        }

        if ($this->isInWishlist($productId, $customerId)) {
            $this->remove($productId, $customerId);
            return ['success' => true, 'action' => 'removed', 'in_wishlist' => false];
        } else {
            $this->add($productId, $customerId);
            return ['success' => true, 'action' => 'added', 'in_wishlist' => true];
        }
    }

    /**
     * Clear all wishlist items for a customer
     */
    public function clear(?int $customerId = null): int
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return 0;
        }

        return Wishlist::where('customer_id', $customerId)->delete();
    }

    /**
     * Get product IDs in customer's wishlist
     */
    public function getWishlistProductIds(?int $customerId = null): array
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return [];
        }

        return Wishlist::where('customer_id', $customerId)
            ->pluck('product_id')
            ->toArray();
    }
}
