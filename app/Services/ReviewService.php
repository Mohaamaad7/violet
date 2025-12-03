<?php

namespace App\Services;

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
 */
class ReviewService
{
    /**
     * Check if user can review a product (must have a delivered order containing it)
     */
    public function canReview(int $productId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        // Check if user has a delivered order with this product
        return Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();
    }

    /**
     * Check if user has already reviewed a product
     */
    public function hasReviewed(int $productId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        return ProductReview::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user's review for a product
     */
    public function getUserReview(int $productId, ?int $userId = null): ?ProductReview
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        return ProductReview::where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get the order ID that allows user to review this product
     */
    public function getReviewableOrderId(int $productId, ?int $userId = null): ?int
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return null;
        }

        $order = Order::where('user_id', $userId)
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
        $userId = Auth::id();
        $productId = $data['product_id'];

        // Verify user can review this product
        if (!$this->canReview($productId, $userId)) {
            throw new \Exception(__('messages.reviews.cannot_review'));
        }

        // Check for existing review
        if ($this->hasReviewed($productId, $userId)) {
            throw new \Exception(__('messages.reviews.already_reviewed'));
        }

        // Get order ID for verified purchase
        $orderId = $this->getReviewableOrderId($productId, $userId);

        return ProductReview::create([
            'product_id' => $productId,
            'user_id' => $userId,
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
        if ($review->user_id !== Auth::id()) {
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
        if ($review->user_id !== Auth::id()) {
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
            ->with('user:id,name');

        // Include user's own unapproved reviews
        $userId = Auth::id();
        if ($userId) {
            $query->orWhere(function ($q) use ($productId, $userId) {
                $q->where('product_id', $productId)
                  ->where('user_id', $userId);
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
     * Get user's reviews
     */
    public function getUserReviews(?int $userId = null, int $perPage = 10): LengthAwarePaginator
    {
        $userId = $userId ?? Auth::id();

        return ProductReview::where('user_id', $userId)
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
