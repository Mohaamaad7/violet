<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CosmeticsProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Clearing existing products...');
            ProductVariant::query()->forceDelete();
            Product::query()->forceDelete();
            
            $this->command->info('Creating Flower Violet products...');
            
            // Get categories
            $bodyLotions = Category::where('slug', 'body-lotions')->first();
            $deodorants = Category::where('slug', 'deodorants')->first();
            $bodySplash = Category::where('slug', 'body-splash')->first();
            $mukhammaria = Category::where('slug', 'mukhammaria')->first();
            
            if (!$bodyLotions || !$deodorants || !$bodySplash || !$mukhammaria) {
                $this->command->error('Categories not found! Please run CosmeticsCategoriesSeeder first.');
                return;
            }
            
            // Counter for statistics
            $productsCount = 0;
            $variantsCount = 0;
            
            // 1. Body Lotions (6 products)
            $lotions = [
                'فلاور فايوليت كوكو دريم لوشن 250مل',
                'فلاور فايوليت بينك جلو لوشن 250مل',
                'فلاور فايوليت اون ذا مون لوشن 250مل',
                'فلاور فايوليت فيرست نايت لوشن 250مل',
                'فلاور فايوليت فلامنج ريد لوشن 250مل',
                'فلاور فايوليت رمان مسك لوشن 250مل',
            ];
            
            $lotionSlugs = [
                'coco-dream-body-lotion-250ml',
                'pink-glow-body-lotion-250ml',
                'on-the-moon-body-lotion-250ml',
                'first-night-body-lotion-250ml',
                'flaming-red-body-lotion-250ml',
                'pomegranate-musk-body-lotion-250ml',
            ];
            
            foreach ($lotions as $index => $name) {
                Product::create([
                    'category_id' => $bodyLotions->id,
                    'name' => $name,
                    'slug' => $lotionSlugs[$index],
                    'sku' => 'FV-LOT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'description' => 'لوشن مرطب للجسم برائحة مميزة من فلاور فايوليت، حجم 250 مل',
                    'short_description' => 'لوشن مرطب للجسم 250 مل',
                    'price' => 85.00,
                    'stock' => 100,
                    'brand' => 'Flower Violet',
                    'status' => 'active',
                    'is_featured' => false,
                ]);
                $productsCount++;
            }
            
            // 2. Deodorants (4 products)
            $deodorantsList = [
                'فلاور فايوليت سيلفر اكس مزيل عرق',
                'فلاور فايوليت فيلفيت ميست مزيل عرق',
                'فلاور فايوليت فريش داي مزيل عرق',
                'فلاور فايوليت بينك بلوم مزيل عرق',
            ];
            
            $deodorantSlugs = [
                'silver-x-deodorant',
                'velvet-mist-deodorant',
                'fresh-day-deodorant',
                'pink-bloom-deodorant',
            ];
            
            foreach ($deodorantsList as $index => $name) {
                Product::create([
                    'category_id' => $deodorants->id,
                    'name' => $name,
                    'slug' => $deodorantSlugs[$index],
                    'sku' => 'FV-DEO-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'description' => 'مزيل عرق بحماية طويلة الأمد ورائحة منعشة من فلاور فايوليت',
                    'short_description' => 'مزيل عرق حماية 48 ساعة',
                    'price' => 45.00,
                    'stock' => 150,
                    'brand' => 'Flower Violet',
                    'status' => 'active',
                    'is_featured' => false,
                ]);
                $productsCount++;
            }
            
            // 3. Body Splash (10 products with 2 variants each)
            $splashList = [
                ['name' => 'فلاور فايوليت بينك جلو بودي سبلاش', 'slug' => 'pink-glow-body-splash'],
                ['name' => 'فلاور فايوليت فلامنج ريد بودي سبلاش', 'slug' => 'flaming-red-body-splash'],
                ['name' => 'فلاور فايوليت اون ذا مون بودي سبلاش', 'slug' => 'on-the-moon-body-splash'],
                ['name' => 'فلاور فايوليت فيرست نايت بودي سبلاش', 'slug' => 'first-night-body-splash'],
                ['name' => 'فلاور فايوليت رمان مسك بودي سبلاش', 'slug' => 'pomegranate-musk-body-splash'],
                ['name' => 'فلاور فايوليت سنو ليجيند بودي سبلاش', 'slug' => 'snow-legend-body-splash'],
                ['name' => 'فلاور فايوليت ايجلز بوند بودي سبلاش', 'slug' => 'eagles-bond-body-splash'],
                ['name' => 'فلاور فايوليت ارابيان نايت بودي سبلاش', 'slug' => 'arabian-knight-body-splash'],
                ['name' => 'فلاور فايوليت عود بودي سبلاش', 'slug' => 'oud-body-splash'],
                ['name' => 'فلاور فايوليت كوكو دريم بودي سبلاش', 'slug' => 'coco-dream-body-splash'],
            ];
            
            foreach ($splashList as $index => $item) {
                $product = Product::create([
                    'category_id' => $bodySplash->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'sku' => 'FV-SPL-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'description' => 'بودي سبلاش منعش برائحة فاخرة من فلاور فايوليت، متوفر بحجمين',
                    'short_description' => 'بودي سبلاش معطر',
                    'price' => 120.00,
                    'stock' => 0, // Stock will be in variants
                    'brand' => 'Flower Violet',
                    'status' => 'active',
                    'is_featured' => false,
                ]);
                $productsCount++;
                
                // Create 2 variants: 240ml and 90ml
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'FV-SPL-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '-240ML',
                    'name' => '240 مل',
                    'price' => 120.00,
                    'stock' => 80,
                    'attributes' => json_encode(['size' => '240ml']),
                ]);
                $variantsCount++;
                
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => 'FV-SPL-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '-90ML',
                    'name' => '90 مل',
                    'price' => 65.00,
                    'stock' => 120,
                    'attributes' => json_encode(['size' => '90ml']),
                ]);
                $variantsCount++;
            }
            
            // 4. Mukhammaria (6 products)
            $mukhammariaList = [
                ['name' => 'فلاور فايوليت أثير مخمرية', 'slug' => 'atheer-mukhammaria'],
                ['name' => 'فلاور فايوليت رمان مسك مخمرية', 'slug' => 'pomegranate-musk-mukhammaria'],
                ['name' => 'فلاور فايوليت كاندي مخمرية', 'slug' => 'candy-mukhammaria'],
                ['name' => 'فلاور فايوليت باشن مخمرية', 'slug' => 'passion-mukhammaria'],
                ['name' => 'فلاور فايوليت سكاندال مخمرية', 'slug' => 'scandal-mukhammaria'],
                ['name' => 'فلاور فايوليت سحر الليالي مخمرية', 'slug' => 'sahar-el-layaly-mukhammaria'],
            ];
            
            foreach ($mukhammariaList as $index => $item) {
                Product::create([
                    'category_id' => $mukhammaria->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'sku' => 'FV-MKH-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'description' => 'مخمرية فاخرة برائحة عربية أصيلة من فلاور فايوليت',
                    'short_description' => 'مخمرية عربية فاخرة',
                    'price' => 95.00,
                    'stock' => 60,
                    'brand' => 'Flower Violet',
                    'status' => 'active',
                    'is_featured' => false,
                ]);
                $productsCount++;
            }
            
            $this->command->info('✅ Products created successfully!');
            $this->displayStatistics($productsCount, $variantsCount);
        });
    }
    
    /**
     * Display statistics after seeding
     */
    private function displayStatistics(int $productsCount, int $variantsCount): void
    {
        $this->command->newLine();
        $this->command->info('📊 Statistics:');
        $this->command->info("   Total Products: {$productsCount}");
        $this->command->info("   Total Variants: {$variantsCount}");
        $this->command->newLine();
        
        $this->command->info('📦 Products by Category:');
        $categories = Category::whereNotNull('parent_id')->withCount('products')->get();
        foreach ($categories as $category) {
            $this->command->info("   ├── {$category->name} → {$category->products_count} products");
        }
        
        $this->command->newLine();
        $this->command->info('✨ Flower Violet products seeded successfully!');
    }
}
