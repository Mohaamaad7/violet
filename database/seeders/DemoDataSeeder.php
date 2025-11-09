<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if categories already exist
        if (Category::count() > 0) {
            $this->command->warn('Categories already exist. Skipping category creation...');
        } else {
            $this->command->info('Creating demo categories...');
            
            // Create main categories
            $categories = [
                'Electronics' => ['Phones', 'Laptops', 'Tablets'],
                'Fashion' => ['Men', 'Women', 'Kids'],
                'Home & Kitchen' => ['Furniture', 'Appliances', 'Decor'],
                'Beauty' => ['Skincare', 'Makeup', 'Haircare'],
                'Sports' => ['Fitness', 'Outdoor', 'Team Sports'],
            ];

            foreach ($categories as $parentName => $children) {
                $parent = Category::create([
                    'name' => $parentName,
                    'slug' => Str::slug($parentName),
                    'description' => "Best $parentName products",
                    'is_active' => true,
                    'order' => 0,
                ]);

                foreach ($children as $childName) {
                    Category::create([
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'slug' => Str::slug($childName),
                        'description' => "Quality $childName",
                        'is_active' => true,
                        'order' => 0,
                    ]);
                }
            }
        }

        // Skip if products already exist
        if (Product::count() > 0) {
            $this->command->warn('Products already exist. Skipping product creation...');
        } else {
            $this->command->info('Creating demo products...');
            
            // Create products for each category
            $categories = Category::whereNotNull('parent_id')->get();
            
            foreach ($categories as $category) {
                Product::factory(10)->create([
                    'category_id' => $category->id,
                ]);
            }
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('Categories: ' . Category::count());
        $this->command->info('Products: ' . Product::count());
    }
}
