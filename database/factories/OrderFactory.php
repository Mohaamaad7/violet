<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $shipping = fake()->randomElement([0, 25, 50]);
        $discount = fake()->randomElement([0, 10, 20, 50]);
        
        return [
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('??####')),
            'user_id' => User::factory(),
            'shipping_address_id' => null, // Will be created separately
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['unpaid', 'paid']), // Fixed: use 'unpaid' not 'pending'
            'payment_method' => 'cod',
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'tax_amount' => 0,
            'total' => $subtotal + $shipping - $discount,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'unpaid', // Fixed: use 'unpaid' not 'pending'
        ]);
    }

    /**
     * Indicate the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(2),
            'shipped_at' => now()->subDays(1),
            'delivered_at' => now(),
        ]);
    }

    /**
     * Indicate the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
