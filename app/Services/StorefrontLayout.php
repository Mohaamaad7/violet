<?php

namespace App\Services;

use App\Enums\StorefrontTemplate;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class StorefrontLayout
{
    public const SETTING_KEY = 'storefront_template';

    public function template(): StorefrontTemplate
    {
        return StorefrontTemplate::tryFrom((string) Setting::get(self::SETTING_KEY))
            ?? StorefrontTemplate::Classic;
    }

    public function isCategoryFirst(): bool
    {
        return $this->template() === StorefrontTemplate::CategoryFirst;
    }

    public function headerCategories(): Collection
    {
        return Category::active()->parent()->where('show_in_header', true)
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    /** Load the active hierarchy once, rather than querying each descendant. */
    public function categoryTree(bool $withProductCounts = false): Collection
    {
        $categories = Category::active()
            ->when($withProductCounts, fn ($query) => $query->withCount([
                'products as active_products_count' => fn ($products) => $products->where('status', 'active'),
            ]))
            ->orderBy('sort_order')->orderBy('order')->orderBy('id')->get();
        $children = $categories->groupBy('parent_id');

        foreach ($categories as $category) {
            $category->setRelation('children', new Collection($children->get($category->id, [])));
        }

        return $categories;
    }

    public function homeSections(): Collection
    {
        $tree = $this->categoryTree();
        $sections = $tree->whereNull('parent_id')->where('show_on_home', true)
            ->sortBy([['home_sort_order', 'asc'], ['sort_order', 'asc'], ['id', 'asc']])->values();

        foreach ($sections as $category) {
            $category->setRelation('products', Product::with(['category', 'media'])->active()
                ->whereIn('category_id', [$category->id, ...$category->getDescendantIds()])
                ->orderByDesc('is_featured')->latest()->orderBy('id')->take(8)->get());
        }

        return new Collection($sections->all());
    }
}
