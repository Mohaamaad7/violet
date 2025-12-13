<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(50, 500);
        
        return [
            'product_id' => Product::factory(),
            'batch_number' => 'BATCH-' . $this->faker->unique()->numerify('######'),
            'quantity_initial' => $quantity,
            'quantity_current' => $quantity,
            'manufacturing_date' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'supplier' => $this->faker->company(),
            'notes' => $this->faker->optional()->sentence(),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the batch is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'quantity_current' => 0,
        ]);
    }

    /**
     * Indicate that the batch is disposed.
     */
    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disposed',
            'quantity_current' => 0,
        ]);
    }

    /**
     * Indicate that the batch is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => $this->faker->dateTimeBetween('now', '+7 days'),
            'status' => 'active',
        ]);
    }
}
