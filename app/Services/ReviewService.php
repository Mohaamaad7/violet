<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Review Service
 * 
 * Handles all review-related business logic including:
 * - Creating reviews (only for delivered orders)
 * - Updating reviews (owner only)
 * - Deleting reviews (owner only)
 * - Fetching reviews with filtering
 * 
 * NOTE: This service uses Customer model, not User model.
 */
class ReviewService
{
    /**
     * Get the currently authenticated customer ID
     */
    private function getCustomerId(?int $customerId = null): ?int
    {
        if ($customerId) {
            return $customerId;
        }

        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->id();
        }

        return null;
    }

    /**
     * Check if customer can review a product (must have a delivered order containing it)
     */
    public function canReview(int $productId, ?int $customerId = null): bool
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return false;
        }

        // Check if customer has a delivered order with this product
        return Order::where('customer_id', $customerId)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();
    }

    /**
     * Check if customer has already reviewed a product
     */
    public function hasReviewed(int $productId, ?int $customerId = null): bool
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return false;
        }

        return ProductReview::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->exists();
    }

    /**
     * Get customer's review for a product
     */
    public function getCustomerReview(int $productId, ?int $customerId = null): ?ProductReview
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return null;
        }

        return ProductReview::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->first();
    }

    /**
     * Get the order ID that allows customer to review this product
     */
    public function getReviewableOrderId(int $productId, ?int $customerId = null): ?int
    {
        $customerId = $this->getCustomerId($customerId);

        if (!$customerId) {
            return null;
        }

        $order = Order::where('customer_id', $customerId)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->first();

        return $order?->id;
    }

    /**
     * Create a new review
     */
    public function create(array $data): ProductReview
    {
        $customerId = $this->getCustomerId();
        $productId = $data['product_id'];

        // Verify customer can review this product
        if (!$this->canReview($productId, $customerId)) {
            throw new \Exception(__('messages.reviews.cannot_review'));
        }

        // Check for existing review
        if ($this->hasReviewed($productId, $customerId)) {
            throw new \Exception(__('messages.reviews.already_reviewed'));
        }

        // Get order ID for verified purchase
        $orderId = $this->getReviewableOrderId($productId, $customerId);

        return ProductReview::create([
            'product_id' => $productId,
            'customer_id' => $customerId,
            'order_id' => $orderId,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_verified_purchase' => true,
            'is_approved' => false, // Requires moderation
        ]);
    }

    /**
     * Update an existing review
     */
    public function update(ProductReview $review, array $data): ProductReview
    {
        // Verify ownership
        $customerId = $this->getCustomerId();
        if ($review->customer_id !== $customerId) {
            throw new \Exception(__('messages.reviews.unauthorized'));
        }

        $review->update([
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? $review->images,
            'is_approved' => false, // Re-submit for moderation after edit
        ]);

        return $review->fresh();
    }

    /**
     * Delete a review
     */
    public function delete(ProductReview $review): bool
    {
        // Verify ownership
        $customerId = $this->getCustomerId();
        if ($review->customer_id !== $customerId) {
            throw new \Exception(__('messages.reviews.unauthorized'));
        }

        return $review->delete();
    }

    /**
     * Get reviews for a product (approved only for public, all for owner)
     */
    public function getProductReviews(
        int $productId,
        int $perPage = 10,
        string $sortBy = 'latest'
    ): LengthAwarePaginator {
        $query = ProductReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->with('customer:id,name');

        // Include customer's own unapproved reviews
        $customerId = $this->getCustomerId();
        if ($customerId) {
            $query->orWhere(function ($q) use ($productId, $customerId) {
                $q->where('product_id', $productId)
                    ->where('customer_id', $customerId);
            });
        }

        // Apply sorting
        $query = match ($sortBy) {
            'oldest' => $query->oldest(),
            'highest' => $query->orderByDesc('rating'),
            'lowest' => $query->orderBy('rating'),
            'helpful' => $query->orderByDesc('helpful_count'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get review statistics for a product
     */
    public function getProductStats(int $productId): array
    {
        $reviews = ProductReview::where('product_id', $productId)
            ->where('is_approved', true);

        $totalCount = $reviews->count();
        $averageRating = $totalCount > 0 ? round($reviews->avg('rating'), 1) : 0;

        // Get distribution
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = ProductReview::where('product_id', $productId)
                ->where('is_approved', true)
                ->where('rating', $i)
                ->count();
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $totalCount > 0 ? round(($count / $totalCount) * 100) : 0,
            ];
        }

        return [
            'total_count' => $totalCount,
            'average_rating' => $averageRating,
            'distribution' => $distribution,
        ];
    }

    /**
     * Get customer's reviews
     */
    public function getCustomerReviews(?int $customerId = null, int $perPage = 10): LengthAwarePaginator
    {
        $customerId = $this->getCustomerId($customerId);

        return ProductReview::where('customer_id', $customerId)
            ->with(['product:id,name,slug', 'product.media'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Mark a review as helpful
     */
    public function markHelpful(ProductReview $review): void
    {
        $review->increment('helpful_count');
    }

    /**
     * Approve a review (admin only)
     */
    public function approve(ProductReview $review): ProductReview
    {
        $review->update(['is_approved' => true]);
        return $review->fresh();
    }

    /**
     * Reject a review (admin only)
     */
    public function reject(ProductReview $review): bool
    {
        return $review->delete();
    }
}
