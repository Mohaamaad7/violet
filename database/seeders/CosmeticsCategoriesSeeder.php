<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            Category::query()->forceDelete(); // Use forceDelete to bypass soft deletes
            
            $this->command->info('Creating cosmetics categories structure...');
            
            // Counter for order
            $mainOrder = 1;
            
            // 1. Skin Care (العناية بالبشرة) - 6 subcategories
            $skinCare = Category::create([
                'name' => 'العناية بالبشرة',
                'slug' => 'skin-care',
                'description' => 'منتجات العناية بالبشرة الفاخرة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $skinCareSubcategories = [
                ['name' => 'غسول ومنظفات', 'slug' => 'cleansers-toners'],
                ['name' => 'الترطيب', 'slug' => 'moisturizers'],
                ['name' => 'السيروم والعلاجات', 'slug' => 'serums-treatments'],
                ['name' => 'واقي الشمس', 'slug' => 'sun-protection'],
                ['name' => 'العناية بالعين والشفاه', 'slug' => 'eye-lip-care'],
                ['name' => 'الأقنعة والمقشرات', 'slug' => 'masks-exfoliators'],
            ];
            
            $this->createSubcategories($skinCare->id, $skinCareSubcategories);
            
            // 2. Hair Care (العناية بالشعر) - 5 subcategories
            $hairCare = Category::create([
                'name' => 'العناية بالشعر',
                'slug' => 'hair-care',
                'description' => 'منتجات العناية بالشعر الاحترافية',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $hairCareSubcategories = [
                ['name' => 'الشامبو والبلسم', 'slug' => 'shampoo-conditioner'],
                ['name' => 'علاجات وماسكات', 'slug' => 'hair-treatments-masks'],
                ['name' => 'زيوت وسيروم', 'slug' => 'hair-oils-serums'],
                ['name' => 'تصفيف الشعر', 'slug' => 'hair-styling'],
                ['name' => 'فروة الرأس', 'slug' => 'scalp-care'],
            ];
            
            $this->createSubcategories($hairCare->id, $hairCareSubcategories);
            
            // 3. Body Care (العناية بالجسم) - 5 subcategories
            $bodyCare = Category::create([
                'name' => 'العناية بالجسم',
                'slug' => 'body-care',
                'description' => 'منتجات العناية بالجسم الكاملة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $bodyCareSubcategories = [
                ['name' => 'الاستحمام', 'slug' => 'bath-shower'],
                ['name' => 'ترطيب الجسم', 'slug' => 'body-moisturizers'],
                ['name' => 'العناية باليدين والقدمين', 'slug' => 'hand-foot-care'],
                ['name' => 'مزيلات العرق', 'slug' => 'deodorants'],
                ['name' => 'إزالة الشعر', 'slug' => 'hair-removal'],
            ];
            
            $this->createSubcategories($bodyCare->id, $bodyCareSubcategories);
            
            // 4. Men's Care (العناية بالرجال) - 3 subcategories
            $mensCare = Category::create([
                'name' => 'العناية بالرجال',
                'slug' => 'mens-care',
                'description' => 'منتجات العناية الرجالية الفاخرة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            $mensCareSubcategories = [
                ['name' => 'العناية بالبشرة للرجال', 'slug' => 'mens-skin-care'],
                ['name' => 'العناية بالشعر للرجال', 'slug' => 'mens-hair-care'],
                ['name' => 'العناية بالجسم للرجال', 'slug' => 'mens-body-care'],
            ];
            
            $this->createSubcategories($mensCare->id, $mensCareSubcategories);
            
            // 5. Sets & Bundles (المجموعات) - No subcategories
            Category::create([
                'name' => 'المجموعات',
                'slug' => 'sets-bundles',
                'description' => 'مجموعات المنتجات المميزة بأسعار خاصة',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 6. Best Sellers (الأكثر مبيعاً) - No subcategories
            Category::create([
                'name' => 'الأكثر مبيعاً',
                'slug' => 'best-sellers',
                'description' => 'المنتجات الأكثر مبيعاً لدينا',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 7. New Arrivals (وصل حديثاً) - No subcategories
            Category::create([
                'name' => 'وصل حديثاً',
                'slug' => 'new-arrivals',
                'description' => 'أحدث المنتجات المضافة إلى متجرنا',
                'order' => $mainOrder++,
                'is_active' => true,
            ]);
            
            // 8. Special Offers (العروض الخاصة) - No subcategories
            Category::create([
                'name' => 'العروض الخاصة',
                'slug' => 'special-offers',
                'description' => 'عروض وخصومات مميزة',
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
