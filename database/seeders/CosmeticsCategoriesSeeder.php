<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CosmeticsCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Clear existing categories
            $this->command->info('Clearing existing categories...');
            Category::query()->forceDelete();
            
            $this->command->info('Creating Flower Violet categories structure...');
            
            $mainOrder = 1;
            
            // 1. Body Care (العناية بالجسم) - 2 subcategories
            $bodyCare = Category::create([
                'name' => 'العناية بالجسم',
                'slug' => 'body-care',
                'description' => 'منتجات العناية بالجسم من فلاور فايوليت',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $bodyCareSubcategories = [
                ['name' => 'لوشن الجسم', 'slug' => 'body-lotions'],
                ['name' => 'مزيلات العرق', 'slug' => 'deodorants'],
            ];
            
            $this->createSubcategories($bodyCare->id, $bodyCareSubcategories);
            
            // 2. Fragrances (العطور والروائح) - 2 subcategories
            $fragrances = Category::create([
                'name' => 'العطور والروائح',
                'slug' => 'fragrances',
                'description' => 'عطور ومعطرات فلاور فايوليت الفاخرة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $fragrancesSubcategories = [
                ['name' => 'بودي سبلاش', 'slug' => 'body-splash'],
                ['name' => 'مخمّرية', 'slug' => 'mukhammaria'],
            ];
            
            $this->createSubcategories($fragrances->id, $fragrancesSubcategories);
            
            // 3. Sets & Bundles (المجموعات)
            Category::create([
                'name' => 'المجموعات',
                'slug' => 'sets-bundles',
                'description' => 'مجموعات فلاور فايوليت المميزة بأسعار خاصة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 4. Best Sellers (الأكثر مبيعاً)
            Category::create([
                'name' => 'الأكثر مبيعاً',
                'slug' => 'best-sellers',
                'description' => 'المنتجات الأكثر مبيعاً من فلاور فايوليت',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 5. New Arrivals (وصل حديثاً)
            Category::create([
                'name' => 'وصل حديثاً',
                'slug' => 'new-arrivals',
                'description' => 'أحدث منتجات فلاور فايوليت',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 6. Special Offers (العروض الخاصة)
            Category::create([
                'name' => 'العروض الخاصة',
                'slug' => 'special-offers',
                'description' => 'عروض وخصومات مميزة على منتجات فلاور فايوليت',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $this->command->info('✅ Categories created successfully!');
            $this->displayStatistics();
        });
    }
    
    /**
     * Create subcategories for a parent category
     */
    private function createSubcategories(int $parentId, array $subcategories): void
    {
        $order = 1;
        foreach ($subcategories as $subcategory) {
            Category::create([
                'parent_id' => $parentId,
                'name' => $subcategory['name'],
                'slug' => $subcategory['slug'],
                'order' => $order++,
                'is_active' => true,
            ]);
        }
    }
    
    /**
     * Display statistics after seeding
     */
    private function displayStatistics(): void
    {
        $totalCategories = Category::count();
        $mainCategories = Category::whereNull('parent_id')->count();
        $subcategories = Category::whereNotNull('parent_id')->count();
        
        $this->command->newLine();
        $this->command->info('📊 Statistics:');
        $this->command->info("   Total Categories: {$totalCategories}");
        $this->command->info("   Main Categories: {$mainCategories}");
        $this->command->info("   Subcategories: {$subcategories}");
        $this->command->newLine();
        
        $this->command->info('📁 Category Structure:');
        
        $parents = Category::whereNull('parent_id')->orderBy('order')->get();
        foreach ($parents as $parent) {
            $childrenCount = $parent->children()->count();
            $this->command->info("   ├── {$parent->name} ({$parent->slug}) → {$childrenCount} children");
        }
        
        $this->command->newLine();
        $this->command->info('✨ Cosmetics categories seeded successfully!');
    }
}
