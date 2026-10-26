<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Livewire\Store\ProductList;
use App\Livewire\Store\SearchBar;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\StorefrontLayout;
use App\Support\LegacyCatalogTranslation;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['responsecache.cache_store' => 'array']);
        $this->withSession(['locale' => 'en']);
        app()->setLocale('en');
    }

    private function category(array $attributes = []): Category
    {
        return Category::factory()->create([
            'name' => 'العناية بالبشرة (Skin Care)', 'slug' => 'skin-care',
            'name_ar' => 'العناية بالبشرة', 'name_en' => 'Skin Care',
            'show_in_header' => true, 'show_on_home' => true, ...$attributes,
        ]);
    }

    private function product(Category $category, array $attributes = []): Product
    {
        return Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Original retained name',
            'name_ar' => 'غسول البشرة', 'name_en' => 'Skin Cleanser',
            'description_ar' => 'وصف الغسول بالعربية', 'description_en' => 'English cleanser description',
            'status' => 'active', 'stock' => 10, 'is_featured' => true, 'sale_price' => null,
            ...$attributes,
        ]);
    }

    public function test_names_and_descriptions_are_selected_without_changing_legacy_values(): void
    {
        $category = $this->category();
        $product = $this->product($category);
        foreach (['ar' => ['غسول البشرة', 'وصف الغسول بالعربية'], 'en' => ['Skin Cleanser', 'English cleanser description']] as $locale => [$name, $description]) {
            app()->setLocale($locale);
            $this->assertSame($name, $product->localized_name);
            $this->assertSame($description, $product->localized_description);
            $this->assertSame('Original retained name', $product->name);
        }
    }

    public function test_cached_home_and_header_show_only_the_requested_catalog_translation(): void
    {
        Setting::set(StorefrontLayout::SETTING_KEY, 'category_first');
        $category = $this->category();
        $this->product($category);
        $this->get('/')->assertOk()->assertSee('Skin Cleanser')->assertSee('Skin Care')
            ->assertDontSee('غسول البشرة')->assertDontSee('العناية بالبشرة');
        $this->from('/')->get('/locale/ar');
        $this->get('/')->assertOk()->assertSee('غسول البشرة')->assertSee('العناية بالبشرة')
            ->assertDontSee('Skin Cleanser')->assertDontSee('Skin Care');
        $this->from('/')->get('/locale/en');
        $this->get('/')->assertOk()->assertSee('Skin Cleanser')->assertDontSee('غسول البشرة');
    }

    public function test_product_translation_edits_clear_cached_html_in_both_languages(): void
    {
        $product = $this->product($this->category());
        $this->get('/')->assertSee('Skin Cleanser');
        $this->withSession(['locale' => 'ar'])->get('/')->assertSee('غسول البشرة');
        $product->update(['name_ar' => 'الغسول الجديد', 'name_en' => 'Updated Cleanser']);
        $this->get('/')->assertSee('الغسول الجديد')->assertDontSee('غسول البشرة');
        $this->withSession(['locale' => 'en'])->get('/')->assertSee('Updated Cleanser')->assertDontSee('Skin Cleanser');
    }

    public function test_creating_and_publishing_product_refreshes_cached_home(): void
    {
        $category = $this->category();
        $this->get('/')->assertOk()->assertDontSee('Skin Cleanser');
        $product = $this->product($category, ['status' => 'draft']);
        $this->get('/')->assertDontSee('Skin Cleanser');
        $product->update(['status' => 'active']);
        $this->get('/')->assertSee('Skin Cleanser');
        $product->delete();
        $this->get('/')->assertDontSee('Skin Cleanser');
    }

    public function test_missing_translation_does_not_show_another_language_or_mixed_legacy_name(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'Original English Name', 'name_ar' => null, 'name_en' => 'English Only',
            'description' => 'Original English description', 'description_ar' => null,
        ]);
        app()->setLocale('ar');
        $this->assertStringStartsWith('قيد الترجمة', $product->localized_name);
        $this->assertSame('', $product->localized_description);
        $this->assertFalse($product->has_complete_name_translations);
        $this->assertSame(1, Product::missingNameTranslations()->count());
    }

    public function test_legacy_extraction_is_conservative_and_preserves_source(): void
    {
        $this->assertSame(['ar' => 'العناية بالبشرة', 'en' => 'Skin Care'], LegacyCatalogTranslation::extract('العناية بالبشرة (Skin Care)'));
        $this->assertSame(['ar' => 'غسول البشرة'], LegacyCatalogTranslation::extract('غسول البشرة'));
        $this->assertSame(['en' => 'Skin Cleanser'], LegacyCatalogTranslation::extract('Skin Cleanser'));
        $this->assertSame([], LegacyCatalogTranslation::extract('اسم Skin مختلط'));
    }

    public function test_search_matches_both_translations_and_displays_current_language(): void
    {
        $this->product($this->category());
        Livewire::test(SearchBar::class)->set('search', 'غسول')
            ->assertSet('results.0.name', 'Skin Cleanser')->assertSet('results.0.category', 'Skin Care');
        Livewire::test(ProductList::class)->set('search', 'غسول')->assertSee('Skin Cleanser');
        $this->withSession(['locale' => 'ar']);
        app()->setLocale('ar');
        Livewire::test(SearchBar::class)->set('search', 'Cleanser')
            ->assertSet('results.0.name', 'غسول البشرة');
    }

    public function test_product_details_and_seo_use_the_current_language(): void
    {
        $product = $this->product($this->category(), [
            'meta_title_ar' => 'عنوان الغسول', 'meta_title_en' => 'Cleanser SEO Title',
            'meta_description_ar' => 'وصف البحث العربي', 'meta_description_en' => 'English search description',
            'specifications_ar' => 'المكونات العربية', 'specifications_en' => 'English ingredients',
        ]);
        $this->get(route('product.show', $product->slug))->assertOk()
            ->assertSee('<title>Cleanser SEO Title</title>', false)
            ->assertSee('English ingredients')->assertDontSee('المكونات العربية');
        $this->withSession(['locale' => 'ar'])->get(route('product.show', $product->slug))->assertOk()
            ->assertSee('<title>عنوان الغسول</title>', false)
            ->assertSee('المكونات العربية')->assertDontSee('English ingredients');
    }

    public function test_migration_backfills_only_known_languages_without_altering_ids_or_original_content(): void
    {
        $category = Category::factory()->create(['name' => 'العناية بالجسم (Body Care)']);
        $product = $this->product($category, ['name' => 'Original English Product']);
        $migration = require database_path('migrations/2026_10_26_000001_add_catalog_translations.php');
        $migration->down();
        $migration->up();
        $this->assertSame('العناية بالجسم', $category->fresh()->name_ar);
        $this->assertSame('Body Care', $category->fresh()->name_en);
        $this->assertSame('العناية بالجسم (Body Care)', $category->fresh()->name);
        $this->assertSame('Original English Product', $product->fresh()->name_en);
        $this->assertNull($product->fresh()->name_ar);
        $this->assertSame($category->id, $product->fresh()->category_id);
        $this->assertSame($product->slug, $product->fresh()->slug);
    }

    public function test_preparation_reuses_legacy_fragrance_root_and_preserves_product_assignments(): void
    {
        $root = $this->category(['slug' => 'Body_mist', 'name' => 'عطور ومعطرات الجسم']);
        $product = $this->product($root);
        $this->artisan('storefront:prepare', ['--activate-category-first' => true])->assertSuccessful();
        $this->assertModelExists($root);
        $this->assertSame($root->id, $product->fresh()->category_id);
        $this->assertFalse(Category::where('slug', 'fragrance')->exists());
        $this->assertCount(3, app(StorefrontLayout::class)->headerCategories());
        $count = Category::count();
        $root->update(['show_in_header' => false, 'sort_order' => 44]);
        Setting::set(StorefrontLayout::SETTING_KEY, 'classic');
        $this->artisan('storefront:prepare', ['--activate-category-first' => true])->assertSuccessful();
        $this->assertDatabaseCount('categories', $count);
        $this->assertFalse($root->fresh()->show_in_header);
        $this->assertSame(44, $root->fresh()->sort_order);
        $this->assertSame('classic', app(StorefrontLayout::class)->template()->value);
    }

    public function test_filament_requires_both_names_for_published_product_but_allows_drafts(): void
    {
        $this->admin();
        $category = $this->category();
        Livewire::test(CreateProduct::class)->fillForm([
            'name_ar' => 'منتج تجريبي', 'name_en' => null, 'slug' => 'published-missing-name',
            'category_id' => $category->id, 'price' => 100, 'stock' => 5, 'status' => 'active',
        ])->call('create')->assertHasFormErrors(['name_en' => 'required']);
        Livewire::test(CreateProduct::class)->fillForm([
            'name_ar' => 'مسودة', 'name_en' => null, 'slug' => 'draft-missing-name',
            'category_id' => $category->id, 'price' => 100, 'stock' => 5, 'status' => 'draft',
        ])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('products', ['slug' => 'draft-missing-name', 'status' => 'draft', 'name_ar' => 'مسودة']);
    }

    public function test_filament_publishes_bilingual_product_and_keeps_original_name_and_slug_on_edit(): void
    {
        $this->admin();
        $product = $this->product($this->category());
        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['name_ar' => 'اسم جديد', 'name_en' => 'New name', 'status' => 'active'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Original retained name', $product->fresh()->name);
        $this->assertSame($product->slug, $product->fresh()->slug);
        $this->assertSame('New name', $product->fresh()->name_en);
    }

    public function test_filament_requires_both_names_for_active_categories(): void
    {
        $this->admin();
        Livewire::test(CreateCategory::class)->fillForm([
            'name_ar' => 'قسم جديد', 'name_en' => null, 'slug' => 'new-category', 'is_active' => true,
        ])->call('create')->assertHasFormErrors(['name_en' => 'required']);
        Livewire::test(CreateCategory::class)->fillForm([
            'name_ar' => 'قسم جديد', 'name_en' => 'New Category', 'slug' => 'new-category', 'is_active' => true,
        ])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('categories', ['slug' => 'new-category', 'name_ar' => 'قسم جديد', 'name_en' => 'New Category']);
    }

    public function test_bulk_publish_does_not_partially_publish_a_selection_with_missing_translations(): void
    {
        $this->admin();
        $category = $this->category();
        $complete = $this->product($category, ['status' => 'draft']);
        $incomplete = $this->product($category, ['status' => 'draft', 'name_en' => null]);
        Livewire::test(ListProducts::class)->callTableBulkAction('publish', [$complete, $incomplete])
            ->assertNotified(__('catalog.missing_translations'));
        $this->assertSame('draft', $complete->fresh()->status);
        $this->assertSame('draft', $incomplete->fresh()->status);
    }

    private function admin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        Gate::before(fn () => true);
    }
}
