<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);
        
        return [
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'image' => null,
            'icon' => null,
            'order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'meta_title' => ucfirst($name),
            'meta_description' => fake()->sentence(),
        ];
    }
}
