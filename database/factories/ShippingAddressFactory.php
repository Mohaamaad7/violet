<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\ShippingAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingAddress>
 */
class ShippingAddressFactory extends Factory
{
    protected $model = ShippingAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $governorates = ['Cairo', 'Giza', 'Alexandria', 'Qalyubia', 'Sharqia', 'Dakahlia'];
        
        return [
            'order_id' => null, // Nullable - for saved addresses without order
            'customer_id' => Customer::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('01#########'),
            'governorate' => fake()->randomElement($governorates),
            'city' => fake()->city(),
            'area' => fake()->streetName(),
            'street_address' => fake()->streetAddress(),
            'building_number' => fake()->buildingNumber(),
            'floor' => (string) fake()->numberBetween(1, 15),
            'apartment' => (string) fake()->numberBetween(1, 20),
            'landmark' => fake()->optional()->sentence(3),
            'is_default' => false,
        ];
    }

    /**
     * Indicate the address is default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
