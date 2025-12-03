<?php

namespace App\Livewire\Store;

use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ReviewService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Product Reviews Component
 * 
 * Displays reviews for a product and allows customers who have
 * purchased and received the product to submit reviews.
 */
class ProductReviews extends Component
{
    use WithPagination;

    public Product $product;
    public int $rating = 5;
    public string $title = '';
    public string $comment = '';
    public string $sortBy = 'latest';
    public bool $showForm = false;
    public bool $isEditing = false;
    public ?int $editingReviewId = null;

    protected ReviewService $reviewService;

    protected function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function boot(ReviewService $reviewService): void
    {
        $this->reviewService = $reviewService;
    }

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function getCanReviewProperty(): bool
    {
        return $this->reviewService->canReview($this->product->id);
    }

    public function getHasReviewedProperty(): bool
    {
        return $this->reviewService->hasReviewed($this->product->id);
    }

    public function getUserReviewProperty(): ?ProductReview
    {
        return $this->reviewService->getUserReview($this->product->id);
    }

    public function getStatsProperty(): array
    {
        return $this->reviewService->getProductStats($this->product->id);
    }

    /**
     * Select a rating and open the review form
     * This is the primary CTA - user clicks a star to start their review
     */
    public function selectRating(int $value): void
    {
        if ($this->hasReviewed) {
            // Edit existing review - preload existing data
            $review = $this->userReview;
            $this->rating = $value; // Use the clicked star value
            $this->title = $review->title ?? '';
            $this->comment = $review->comment ?? '';
            $this->isEditing = true;
            $this->editingReviewId = $review->id;
        } else {
            // New review - start fresh with clicked rating
            $this->reset(['title', 'comment']);
            $this->rating = $value;
            $this->isEditing = false;
            $this->editingReviewId = null;
        }
        $this->showForm = true;
    }

    public function openForm(): void
    {
        if ($this->hasReviewed) {
            // Edit existing review
            $review = $this->userReview;
            $this->rating = $review->rating;
            $this->title = $review->title ?? '';
            $this->comment = $review->comment ?? '';
            $this->isEditing = true;
            $this->editingReviewId = $review->id;
        } else {
            // New review
            $this->reset(['rating', 'title', 'comment']);
            $this->rating = 5;
            $this->isEditing = false;
            $this->editingReviewId = null;
        }
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->isEditing = false;
        $this->editingReviewId = null;
        $this->resetValidation();
    }

    public function submit(): void
    {
        $this->validate();

        try {
            if ($this->isEditing && $this->editingReviewId) {
                $review = ProductReview::findOrFail($this->editingReviewId);
                $this->reviewService->update($review, [
                    'rating' => $this->rating,
                    'title' => $this->title,
                    'comment' => $this->comment,
                ]);
                
                $this->dispatch('review-updated');
                session()->flash('success', __('messages.reviews.updated'));
            } else {
                $this->reviewService->create([
                    'product_id' => $this->product->id,
                    'rating' => $this->rating,
                    'title' => $this->title,
                    'comment' => $this->comment,
                ]);
                
                $this->dispatch('review-created');
                session()->flash('success', __('messages.reviews.submitted'));
            }

            $this->closeForm();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteReview(): void
    {
        try {
            $review = $this->userReview;
            if ($review) {
                $this->reviewService->delete($review);
                $this->dispatch('review-deleted');
                session()->flash('success', __('messages.reviews.deleted'));
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function markHelpful(int $reviewId): void
    {
        $review = ProductReview::find($reviewId);
        if ($review) {
            $this->reviewService->markHelpful($review);
        }
    }

    public function render()
    {
        $reviews = $this->reviewService->getProductReviews(
            $this->product->id,
            10,
            $this->sortBy
        );

        return view('livewire.store.product-reviews', [
            'reviews' => $reviews,
        ]);
    }
}
