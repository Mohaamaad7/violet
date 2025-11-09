<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 50, 1000);
        $hasSale = fake()->boolean(30);
        
        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => 'PRD-' . strtoupper(fake()->bothify('???-###')),
            'description' => fake()->paragraph(5),
            'short_description' => fake()->sentence(),
            'price' => $price,
            'sale_price' => $hasSale ? $price * 0.8 : null,
            'cost_price' => $price * 0.6,
            'stock' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'weight' => fake()->randomFloat(2, 0.1, 5),
            'brand' => fake()->company(),
            'barcode' => fake()->ean13(),
            'status' => fake()->randomElement(['active', 'active', 'active', 'draft']),
            'is_featured' => fake()->boolean(20),
            'views_count' => fake()->numberBetween(0, 1000),
            'sales_count' => fake()->numberBetween(0, 100),
            'meta_title' => ucfirst($name),
            'meta_description' => fake()->sentence(),
        ];
    }
}
