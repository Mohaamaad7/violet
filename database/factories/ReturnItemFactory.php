<?php

namespace Database\Factories;

use App\Models\OrderReturn;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnItem>
 */
class ReturnItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 10, 200);
        
        return [
            'return_id' => OrderReturn::factory(),
            'order_item_id' => OrderItem::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $quantity,
            'price' => $price,
            'condition' => $this->faker->randomElement(['good', 'opened', 'damaged']),
            'restocked' => false,
            'restocked_at' => null,
        ];
    }

    /**
     * Indicate that the item has been restocked.
     */
    public function restocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'good',
            'restocked' => true,
            'restocked_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the item is in good condition.
     */
    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'good',
        ]);
    }

    /**
     * Indicate that the item is damaged.
     */
    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'damaged',
            'restocked' => false,
        ]);
    }
}
