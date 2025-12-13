<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderReturn>
 */
class OrderReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'return_number' => 'RET-' . date('Ymd') . '-' . $this->faker->unique()->numerify('####'),
            'type' => $this->faker->randomElement(['rejection', 'return_after_delivery']),
            'status' => 'pending',
            'reason' => $this->faker->randomElement(['defective', 'wrong_item', 'not_as_described', 'damaged', 'other']),
            'customer_notes' => $this->faker->optional()->paragraph(),
            'admin_notes' => null,
            'refund_amount' => 0,
            'refund_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'completed_by' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the return is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'admin_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the return is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-14 days', '-7 days'),
            'completed_by' => User::factory(),
            'completed_at' => $this->faker->dateTimeBetween('-6 days', 'now'),
            'refund_amount' => $this->faker->randomFloat(2, 50, 500),
            'refund_status' => 'completed',
        ]);
    }

    /**
     * Indicate that the return is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'admin_notes' => $this->faker->sentence(),
        ]);
    }
}
