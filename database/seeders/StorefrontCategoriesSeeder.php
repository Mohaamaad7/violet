<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Services\StorefrontLayout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

class StorefrontCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Reuse existing roots (including the original plural fragrance slug).
            // Never delete, rename, reparent, deactivate, or move existing products.
            $definitions = [
                ['Body Care', ['body-care', 'body_care'], 1, 2],
                ['Skin Care', ['skin-care', 'skincare', 'Skin_Care', 'skin_care'], 2, 3],
                ['Fragrance', ['fragrance', 'fragrances'], 3, 1],
            ];

            foreach ($definitions as [$name, $slugs, $headerOrder, $homeOrder]) {
                $category = Category::query()->parent()->whereIn('slug', $slugs)->first();
                if (! $category) {
                    $category = Category::create([
                        'name' => $name, 'slug' => $slugs[0],
                        'is_active' => true, 'order' => $headerOrder,
                    ]);
                }

                $category->update([
                    'is_active' => true,
                    'show_in_header' => true, 'show_on_home' => true,
                    'sort_order' => $headerOrder, 'home_sort_order' => $homeOrder,
                ]);

                if ($name === 'Body Care') {
                    $this->child($category, 'Musk', ['musk', 'msk', 'المسك'], 1);
                    $this->child($category, 'Mukhammaria', ['mukhammaria', 'mkhmry', 'المخمرية', 'مخمّرية'], 2);
                    $this->child($category, 'Body Lotion', ['body-lotions', 'body-lotion', 'body-moisturizers', 'لوشن الجسم'], 3);
                } elseif ($name === 'Skin Care') {
                    $this->child($category, 'Cleanser', ['cleanser', 'cleansers', 'cleansers_toners', 'الغسول'], 1);
                } else {
                    $women = $this->child($category, 'Women', ['women', 'women-fragrances', 'نساء'], 1);
                    $men = $this->child($category, 'Men', ['men', 'men-fragrances', 'رجال'], 2);
                    foreach ([$women, $men] as $gender) {
                        $this->child($gender, '90 ml', ['90-ml', '90ml', '90 مل'], 1);
                        $this->child($gender, '240 ml', ['240-ml', '240ml', '240 مل'], 2);
                    }
                }
            }

            Setting::firstOrCreate(['key' => StorefrontLayout::SETTING_KEY], [
                'value' => 'classic', 'type' => 'string', 'group' => 'general',
            ]);
        });

        // Also works when called by DatabaseSeeder with model events disabled.
        ResponseCache::clear();
    }

    private function child(Category $parent, string $name, array $aliases, int $order): Category
    {
        $slug = $parent->slug.'-'.$aliases[0];

        $slugs = [$slug, ...$aliases, ...array_map(fn ($alias) => $parent->slug.'-'.$alias, $aliases)];

        return $parent->children()->where(function ($query) use ($slugs, $name, $aliases) {
            $query->whereIn('slug', $slugs)->orWhereIn('name', [$name, ...$aliases]);
        })->first() ?? Category::create([
            'parent_id' => $parent->id, 'name' => $name, 'slug' => $slug,
            'is_active' => true, 'order' => $order, 'sort_order' => $order,
        ]);
    }
}
