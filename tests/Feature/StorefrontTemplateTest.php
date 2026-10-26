<?php

namespace Tests\Feature;

use App\Filament\Pages\StorefrontSettings;
use App\Livewire\Store\ProductList;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\StorefrontLayout;
use Database\Seeders\StorefrontCategoriesSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StorefrontTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.cache_store' => 'array']);
        app()->setLocale('en');
    }

    private function category(string $name, string $slug, ?Category $parent = null, array $extra = []): Category
    {
        return Category::create([
            'name' => $name, 'slug' => $slug, 'parent_id' => $parent?->id,
            'is_active' => true, 'order' => 1, ...$extra,
        ]);
    }

    private function product(Category $category, string $name): Product
    {
        return Product::factory()->create([
            'category_id' => $category->id, 'name' => $name, 'status' => 'active',
            'stock' => 10, 'sale_price' => null, 'is_featured' => false,
        ]);
    }

    private function categoryFirst(): void
    {
        Setting::set(StorefrontLayout::SETTING_KEY, 'category_first');
    }

    private function header(): string
    {
        return view('components.store.header')->render();
    }

    public function test_switching_templates_updates_header_and_invalidates_cached_home(): void
    {
        $body = $this->category('Body Care', 'body-care', extra: ['show_in_header' => true, 'sort_order' => 1]);
        $skin = $this->category('Skin Care', 'skin-care', extra: ['show_in_header' => true, 'sort_order' => 2]);
        $fragrance = $this->category('Fragrance', 'fragrance', extra: ['show_in_header' => true, 'sort_order' => 3]);
        $hair = $this->category('Hair Care', 'hair-care');
        $this->category('Inactive', 'inactive', extra: ['is_active' => false, 'show_in_header' => true]);

        $this->get('/')->assertOk()->assertSee('<ul class="dropdown-menu', false);
        $this->categoryFirst();
        $this->get('/')->assertOk()->assertDontSee('<ul class="dropdown-menu', false);
        $header = $this->header();
        $this->assertStringNotContainsString('id="mobile-categories-list"', $header);
        $this->assertStringNotContainsString('hair-care', $header);
        $this->assertStringNotContainsString('category=inactive', $header);

        foreach (['<nav class="hidden lg:block', '<div id="mobile-menu"'] as $start) {
            $navigation = substr($header, strpos($header, $start));
            $navigation = substr($navigation, 0, strpos($navigation, '</nav>'));
            $previous = -1;
            foreach (['href="/"', 'href="/products"', 'category=body-care', 'category=skin-care', 'category=fragrance', 'href="/offers"', 'href="/about"', 'href="/contact"'] as $link) {
                $position = strpos($navigation, $link);
                $this->assertNotFalse($position, $link);
                $this->assertGreaterThan($previous, $position, $link);
                $previous = $position;
            }
        }

        Setting::set(StorefrontLayout::SETTING_KEY, 'classic');
        $this->get('/')->assertOk()->assertSee('<ul class="dropdown-menu', false);
        $this->assertStringContainsString('hair-care', $this->header());
        $this->assertModelExists($hair);
        $this->assertDatabaseCount('categories', 5);
    }

    public function test_unknown_or_missing_template_uses_classic(): void
    {
        $this->assertFalse(app(StorefrontLayout::class)->isCategoryFirst());
        Setting::set(StorefrontLayout::SETTING_KEY, 'unknown');
        $this->assertFalse(app(StorefrontLayout::class)->isCategoryFirst());
    }

    public function test_home_sections_follow_home_order_and_prioritize_only_first_four_images(): void
    {
        $this->categoryFirst();
        $body = $this->category('Body Care', 'body-care', extra: ['show_on_home' => true, 'home_sort_order' => 2]);
        $skin = $this->category('Skin Care', 'skin-care', extra: ['show_on_home' => true, 'home_sort_order' => 3]);
        $fragrance = $this->category('Fragrance', 'fragrance', extra: ['show_on_home' => true, 'home_sort_order' => 1]);
        $leaf = $this->category('90 ml', 'fragrance-women-90', $fragrance);
        for ($i = 1; $i <= 5; $i++) {
            $this->product($leaf, 'Fragrance Product '.$i);
        }
        $this->product($body, 'Body Product');
        $this->product($skin, 'Skin Product');
        $this->product($this->category('Hidden', 'hidden'), 'Hidden Product');

        $response = $this->get('/')->assertOk();
        $response->assertSeeInOrder([
            'data-home-category="fragrance"', 'data-home-category="body-care"',
            'data-home-category="skin-care"', __('messages.new_arrivals'),
        ], false);
        $response->assertDontSee('data-home-category="hidden"', false);
        $response->assertSee('<div class="hidden md:block">', false);
        $html = $response->getContent();
        $section = substr($html, strpos($html, 'data-home-category="fragrance"'));
        $section = substr($section, 0, strpos($section, '</section>'));
        preg_match_all('/<img\b[^>]*alt="Fragrance Product [^"]*"[^>]*>/s', $section, $images);
        $this->assertCount(5, $images[0]);
        foreach (array_slice($images[0], 0, 4) as $image) {
            $this->assertStringContainsString('fetchpriority="high"', $image);
            $this->assertStringNotContainsString('loading="lazy"', $image);
        }
        $this->assertStringContainsString('loading="lazy"', $images[0][4]);
        $this->assertSame(4, substr_count($html, 'fetchpriority="high"'));
    }

    public function test_first_nonempty_home_category_gets_image_priority(): void
    {
        $this->categoryFirst();
        $this->category('Empty', 'empty', extra: ['show_on_home' => true, 'home_sort_order' => 1]);
        $body = $this->category('Body Care', 'body-care', extra: ['show_on_home' => true, 'home_sort_order' => 2]);
        $this->product($body, 'First Visible Product');
        $this->get('/')->assertOk()->assertSee('fetchpriority="high"', false);
    }

    public function test_classic_category_page_is_preserved_and_new_template_opens_product_straps(): void
    {
        $body = $this->category('Body Care', 'body-care');
        $this->category('Musk', 'musk', $body);
        $this->get(route('category.show', $body->slug))->assertOk()->assertSee('Musk');
        Livewire::withQueryParams(['category' => $body->slug])->test(ProductList::class)
            ->assertDontSee('data-category-strap', false);

        $this->categoryFirst();
        $this->get(route('category.show', $body->slug))
            ->assertRedirect(route('products.index', ['category' => $body->slug]));
        $this->get(route('products.index', ['category' => $body->slug]))
            ->assertOk()->assertSee('data-category-strap="body-care"', false);
    }

    public function test_body_and_skin_straps_filter_actual_descendants_and_reset_pagination(): void
    {
        $this->categoryFirst();
        foreach (['Body Care' => 'Musk', 'Skin Care' => 'Cleanser'] as $name => $childName) {
            $slug = str($name)->slug()->toString();
            $root = $this->category($name, $slug);
            $child = $this->category($childName, $slug.'-child', $root);
            $sibling = $this->category('Other '.$name, $slug.'-other', $root);
            $inactive = $this->category('Inactive '.$name, $slug.'-inactive', $root, ['is_active' => false]);
            $product = $this->product($child, $childName.' Product');
            $other = $this->product($sibling, 'Other '.$name.' Product');

            $component = Livewire::withQueryParams(['category' => $slug])->test(ProductList::class)
                ->assertSee('wire:click="selectStrapCategory('.$child->id.')"', false)
                ->assertDontSee('wire:click="selectStrapCategory('.$inactive->id.')"', false)
                ->call('setPage', 2)
                ->call('selectStrapCategory', $child->id)
                ->assertSet('selectedCategories', [$child->id])
                ->assertSet('paginators.page', 1)
                ->assertSee($product->name)->assertDontSee($other->name)
                ->call('clearFilters')->assertSet('selectedCategories', [$root->id])
                ->assertSee($other->name);
        }
    }

    public function test_fragrance_two_level_strap_replaces_branch_and_size_filters(): void
    {
        $this->categoryFirst();
        $root = $this->category('Fragrance', 'fragrance');
        $women = $this->category('Women', 'women', $root);
        $men = $this->category('Men', 'men', $root);
        $women90 = $this->category('90 ml', 'women-90', $women);
        $women240 = $this->category('240 ml', 'women-240', $women);
        $men90 = $this->category('90 ml', 'men-90', $men);
        $men240 = $this->category('240 ml', 'men-240', $men);
        foreach ([$women90, $women240, $men90, $men240] as $category) {
            $this->product($category, 'Product '.$category->slug);
        }

        Livewire::withQueryParams(['category' => 'fragrance'])->test(ProductList::class)
            ->assertSee('data-strap-level="1"', false)->assertDontSee('data-strap-level="2"', false)
            ->call('selectStrapCategory', $women->id)->assertSee('data-strap-level="2"', false)
            ->assertSee('Product women-90')->assertSee('Product women-240')->assertDontSee('Product men-90')
            ->call('selectStrapCategory', $women90->id)
            ->assertSet('selectedCategories', [$women90->id])
            ->assertSee('Product women-90')->assertDontSee('Product women-240')
            ->call('selectStrapCategory', $men->id)
            ->assertSet('selectedCategories', [$men->id])
            ->assertSee('Product men-90')->assertSee('Product men-240')->assertDontSee('Product women-90')
            ->call('selectStrapCategory', $men240->id)
            ->assertSee('Product men-240')->assertDontSee('Product men-90')
            ->call('selectStrapCategory', $root->id)
            ->assertDontSee('data-strap-level="2"', false)->assertSee('Product women-90');
    }

    public function test_url_restores_leaf_filter_without_adding_root_and_broadening_results(): void
    {
        $this->categoryFirst();
        $root = $this->category('Fragrance', 'fragrance');
        $women = $this->category('Women', 'women', $root);
        $size = $this->category('90 ml', 'women-90', $women);
        $other = $this->category('240 ml', 'women-240', $women);
        $this->product($size, 'Selected Size');
        $this->product($other, 'Other Size');
        foreach (['fragrance', 'women-90'] as $slug) {
            Livewire::withQueryParams(['category' => $slug, 'selectedCategories' => [$size->id]])
                ->test(ProductList::class)->assertSet('selectedCategories', [$size->id])
                ->assertSee('Selected Size')->assertDontSee('Other Size')
                ->assertSee('data-strap-level="2"', false);
        }
    }

    public function test_strap_rejects_inactive_and_unrelated_category_ids(): void
    {
        $this->categoryFirst();
        $root = $this->category('Body Care', 'body-care');
        $inactive = $this->category('Inactive', 'inactive', $root, ['is_active' => false]);
        $unrelated = $this->category('Hair Care', 'hair-care');
        foreach ([$inactive, $unrelated] as $category) {
            Livewire::withQueryParams(['category' => $root->slug])->test(ProductList::class)
                ->call('selectStrapCategory', $category->id)->assertStatus(404);
        }
    }

    public function test_category_visibility_changes_clear_the_response_cache(): void
    {
        $this->categoryFirst();
        $category = $this->category('Body Care', 'body-care', extra: ['show_in_header' => true]);
        $this->get('/')->assertOk()->assertSee('category=body-care', false);
        $category->update(['show_in_header' => false]);
        $this->get('/')->assertOk()->assertDontSee('category=body-care', false);
    }

    public function test_seeder_is_repeatable_preserves_existing_categories_and_template(): void
    {
        $hair = $this->category('Hair Care', 'hair-care');
        $sets = $this->category('Sets & Bundles', 'sets-bundles');
        $men = $this->category("Men's Care", 'mens-care');
        $fragrance = $this->category('العطور والروائح', 'fragrances');
        $product = $this->product($hair, 'Existing Hair Product');
        $this->categoryFirst();
        $this->seed(StorefrontCategoriesSeeder::class);
        $count = Category::count();
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->assertDatabaseCount('categories', $count);
        foreach ([$hair, $sets, $men, $fragrance, $product] as $model) {
            $this->assertModelExists($model);
        }
        $this->assertSame($hair->id, $product->fresh()->category_id);
        $this->assertSame('العطور والروائح', $fragrance->fresh()->name);
        $this->assertSame(['body-care', 'skin-care', 'fragrances'], app(StorefrontLayout::class)->headerCategories()->pluck('slug')->all());
        $this->assertSame(['fragrances', 'body-care', 'skin-care'], app(StorefrontLayout::class)->homeSections()->pluck('slug')->all());
        $this->assertTrue(app(StorefrontLayout::class)->isCategoryFirst());
    }

    public function test_filament_can_save_valid_templates_and_rejects_invalid_values(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('super-admin', 'web'));
        $this->actingAs($user);

        Livewire::test(StorefrontSettings::class)->fillForm(['template' => 'category_first'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertTrue(app(StorefrontLayout::class)->isCategoryFirst());
        Livewire::test(StorefrontSettings::class)->fillForm(['template' => 'classic'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertFalse(app(StorefrontLayout::class)->isCategoryFirst());
        Livewire::test(StorefrontSettings::class)->fillForm(['template' => 'invalid'])
            ->call('save')->assertHasFormErrors(['template']);
    }

    public function test_legacy_catalog_slugs_are_reused_without_duplicating_categories(): void
    {
        $body = $this->category('العناية بالجسم (Body Care)', 'body_care');
        $skin = $this->category('العناية بالبشرة (Skin Care)', 'Skin_Care');
        $this->category('المسك (Musk)', 'msk', $body);
        $this->category('المخمرية (Makhmariya)', 'mkhmry', $body);
        $this->category('البودي لوشن (Body Lotion)', 'body-moisturizers', $body);
        $this->category('الغسول (Cleansers)', 'cleansers_toners', $skin);
        $fragrance = $this->category('Fragrance', 'fragrance');
        foreach (['women', 'men'] as $gender) {
            $parent = $this->category($gender, 'fragrance-'.$gender, $fragrance);
            $this->category('90 ml', $parent->slug.'-90ml', $parent);
            $this->category('240 ml', $parent->slug.'-240ml', $parent);
        }
        $count = Category::count();
        $this->seed(StorefrontCategoriesSeeder::class);
        $this->assertDatabaseCount('categories', $count);
        $this->assertSame(['body_care', 'Skin_Care', 'fragrance'], app(StorefrontLayout::class)->headerCategories()->pluck('slug')->all());
    }

    public function test_inactive_category_urls_are_not_available_in_new_template(): void
    {
        $this->categoryFirst();
        $root = $this->category('Inactive', 'inactive', extra: ['is_active' => false]);
        $child = $this->category('Child', 'inactive-child', $root);
        $this->get(route('category.show', $root->slug))->assertNotFound();
        foreach ([$root, $child] as $category) {
            $this->get(route('products.index', ['category' => $category->slug]))->assertNotFound();
        }
    }

    public function test_strap_preserves_search_and_price_filters(): void
    {
        $this->categoryFirst();
        $root = $this->category('Body Care', 'body-care');
        $child = $this->category('Musk', 'musk', $root);
        $cheap = $this->product($child, 'Musk Cheap');
        $cheap->update(['price' => 100]);
        $expensive = $this->product($child, 'Musk Expensive');
        $expensive->update(['price' => 500]);

        Livewire::withQueryParams(['category' => $root->slug])->test(ProductList::class)
            ->set('search', 'Musk')->set('maxPrice', '200')
            ->call('selectStrapCategory', $child->id)
            ->assertSet('search', 'Musk')->assertSet('maxPrice', '200')
            ->assertSee('Musk Cheap')->assertDontSee('Musk Expensive');
    }

    public function test_standalone_home_supports_new_sections_and_keeps_hero_wrapper(): void
    {
        $this->categoryFirst();
        $root = $this->category('Body Care', 'body-care', extra: ['show_on_home' => true]);
        $this->product($root, 'Standalone Product');
        $html = view('store.home')->render();
        $this->assertStringContainsString('<div class="hidden md:block">', $html);
        $this->assertStringContainsString('data-home-category="body-care"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
    }

    public function test_arabic_strap_labels_and_touch_targets_are_available(): void
    {
        $this->categoryFirst();
        $root = $this->category('العناية بالجسم', 'body-care');
        $this->category('المسك', 'musk', $root);
        app()->setLocale('ar');
        Livewire::withQueryParams(['category' => $root->slug])->test(ProductList::class)
            ->assertSee('الأقسام الفرعية')->assertSee('الكل')
            ->assertSee('min-h-[44px] min-w-[44px]', false)
            ->assertSee('aria-pressed="true"', false);
    }

    public function test_home_product_queries_do_not_grow_per_card(): void
    {
        $roots = collect();
        foreach (['Body Care', 'Skin Care', 'Fragrance'] as $name) {
            $root = $this->category($name, str($name)->slug()->toString(), extra: ['show_on_home' => true]);
            $this->product($root, $name.' Product');
            $roots->push($root);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        app(StorefrontLayout::class)->homeSections();
        $initialQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        foreach ($roots as $root) {
            for ($i = 1; $i <= 7; $i++) {
                $this->product($root, $root->name.' Product '.$i);
            }
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        foreach (app(StorefrontLayout::class)->homeSections() as $section) {
            foreach ($section->products as $product) {
                $product->category->name;
                $product->getMedia('product-images');
            }
        }
        $this->assertSame($initialQueries, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }
}
