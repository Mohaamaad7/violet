<?php

namespace App\Livewire\Store\Account;

use App\Models\ProductReview;
use App\Services\ReviewService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * My Reviews Component
 * 
 * Displays all reviews submitted by the logged-in user
 * in their account area. Allows editing and deleting.
 */
class MyReviews extends Component
{
    use WithPagination;

    public int $rating = 5;
    public string $title = '';
    public string $comment = '';
    public bool $showEditModal = false;
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

    public function editReview(int $reviewId): void
    {
        $review = ProductReview::where('id', $reviewId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->editingReviewId = $review->id;
        $this->rating = $review->rating;
        $this->title = $review->title ?? '';
        $this->comment = $review->comment ?? '';
        $this->showEditModal = true;
    }

    public function closeModal(): void
    {
        $this->showEditModal = false;
        $this->editingReviewId = null;
        $this->reset(['rating', 'title', 'comment']);
        $this->resetValidation();
    }

    public function updateReview(): void
    {
        $this->validate();

        try {
            $review = ProductReview::findOrFail($this->editingReviewId);
            $this->reviewService->update($review, [
                'rating' => $this->rating,
                'title' => $this->title,
                'comment' => $this->comment,
            ]);

            session()->flash('success', __('messages.reviews.updated'));
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteReview(int $reviewId): void
    {
        try {
            $review = ProductReview::findOrFail($reviewId);
            $this->reviewService->delete($review);
            session()->flash('success', __('messages.reviews.deleted'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function render()
    {
        $reviews = $this->reviewService->getUserReviews(auth()->id(), 10);

        return view('livewire.store.account.my-reviews', [
            'reviews' => $reviews,
        ])->layout('layouts.store', [
            'title' => __('messages.account.my_reviews'),
        ]);
    }
}
