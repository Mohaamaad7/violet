<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductReview>
 */
class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->optional(0.7)->sentence(4),
            'comment' => fake()->optional(0.8)->paragraph(),
            'images' => null,
            'is_verified_purchase' => false,
            'is_approved' => false,
            'helpful_count' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the review is verified purchase.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => true,
        ]);
    }

    /**
     * Indicate that the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the review is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Create a review with a specific rating.
     */
    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => min(5, max(1, $rating)),
        ]);
    }

    /**
     * Create a review linked to an order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'is_verified_purchase' => true,
        ]);
    }

    /**
     * Create a review with images.
     */
    public function withImages(int $count = 2): static
    {
        $images = [];
        for ($i = 0; $i < $count; $i++) {
            $images[] = 'reviews/' . fake()->uuid() . '.jpg';
        }

        return $this->state(fn (array $attributes) => [
            'images' => $images,
        ]);
    }
}
