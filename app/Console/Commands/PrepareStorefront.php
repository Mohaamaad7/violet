<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\StorefrontLayout;
use Database\Seeders\StorefrontCategoriesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

class PrepareStorefront extends Command
{
    protected $signature = 'storefront:prepare {--activate-category-first : Select category first on initial setup only}';

    protected $description = 'Initialize storefront category visibility once, preserving existing catalog IDs and later admin choices';

    public function handle(StorefrontLayout $layout): int
    {
        DB::transaction(function () {
            if (Setting::get('storefront_catalog_setup_v2')) {
                $this->info('Storefront is already configured. Existing visibility, order, and template choices were preserved.');

                return;
            }
            app(StorefrontCategoriesSeeder::class)->run();
            if ($this->option('activate-category-first')) {
                Setting::set(StorefrontLayout::SETTING_KEY, 'category_first');
            }
            Setting::set('storefront_catalog_setup_v2', '1', 'string', 'general');
            $this->info('Storefront categories initialized without changing existing product assignments.');
        });
        ResponseCache::clear();
        $this->table(['Template', 'Header category', 'ID'], $layout->headerCategories()->map(fn ($category) => [
            $layout->template()->value, $category->name_en ?: $category->name, $category->id,
        ])->all());
        $this->warn('Products missing one or both name translations: '.Product::missingNameTranslations()->count());
        $this->warn('Categories missing one or both name translations: '.Category::missingNameTranslations()->count());

        return self::SUCCESS;
    }
}
