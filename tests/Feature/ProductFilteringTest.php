<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Store\ProductList;

/**
 * Task 5.1: Advanced Search & Filtering System - Feature Tests
 * 
 * Tests for the ProductList Livewire component with advanced filtering capabilities.
 */
class ProductFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Category $childCategory;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create parent category
        $this->category = Category::factory()->create([
            'name' => 'Flowers',
            'slug' => 'flowers',
            'is_active' => true,
            'parent_id' => null,
        ]);
        
        // Create child category
        $this->childCategory = Category::factory()->create([
            'name' => 'Roses',
            'slug' => 'roses',
            'is_active' => true,
            'parent_id' => $this->category->id,
        ]);
    }

    /** @test */
    public function it_can_render_product_list_component(): void
    {
        Product::factory()->count(5)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->assertStatus(200)
            ->assertViewHas('products');
    }

    /** @test */
    public function it_can_filter_products_by_category(): void
    {
        // Create products in different categories
        Product::factory()->count(3)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->count(2)->create([
            'status' => 'active',
            'category_id' => $this->childCategory->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('selectedCategories', [$this->category->id])
            ->assertViewHas('products', function ($products) {
                // Should include products from parent category (and possibly children)
                return $products->count() >= 3;
            });
    }

    /** @test */
    public function it_can_filter_products_by_price_range(): void
    {
        // Create products with different prices
        Product::factory()->create([
            'status' => 'active',
            'price' => 50,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'price' => 150,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'price' => 250,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('minPrice', '100')
            ->set('maxPrice', '200')
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1;
            });
    }

    /** @test */
    public function it_can_filter_products_by_brand(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'brand' => 'Rose Garden',
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'brand' => 'Violet Blooms',
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('selectedBrands', ['Rose Garden'])
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1 && 
                       $products->first()->brand === 'Rose Garden';
            });
    }

    /** @test */
    public function it_can_filter_products_on_sale(): void
    {
        // Create product on sale
        Product::factory()->create([
            'status' => 'active',
            'price' => 100,
            'sale_price' => 80,
            'category_id' => $this->category->id,
        ]);
        
        // Create product not on sale
        Product::factory()->create([
            'status' => 'active',
            'price' => 100,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('onSaleOnly', true)
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1 && 
                       $products->first()->sale_price !== null;
            });
    }

    /** @test */
    public function it_can_filter_products_by_stock_status(): void
    {
        // Create in-stock product
        Product::factory()->create([
            'status' => 'active',
            'stock' => 10,
            'category_id' => $this->category->id,
        ]);
        
        // Create out-of-stock product
        Product::factory()->create([
            'status' => 'active',
            'stock' => 0,
            'category_id' => $this->category->id,
        ]);

        // Test in_stock filter
        Livewire::test(ProductList::class)
            ->set('stockStatus', 'in_stock')
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1 && 
                       $products->first()->stock > 0;
            });

        // Test out_of_stock filter
        Livewire::test(ProductList::class)
            ->set('stockStatus', 'out_of_stock')
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1 && 
                       $products->first()->stock <= 0;
            });
    }

    /** @test */
    public function it_can_filter_products_by_rating(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'average_rating' => 5.0,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'average_rating' => 3.0,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'average_rating' => 2.0,
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('selectedRating', 4)
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1;
            });
    }

    /** @test */
    public function it_can_search_products_by_keyword(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'name' => 'Red Rose Bouquet',
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'name' => 'White Lily Arrangement',
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('search', 'Rose')
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1 && 
                       str_contains($products->first()->name, 'Rose');
            });
    }

    /** @test */
    public function it_can_sort_products_by_price(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'name' => 'Cheap Product',
            'price' => 50,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'name' => 'Expensive Product',
            'price' => 200,
            'sale_price' => null,
            'category_id' => $this->category->id,
        ]);

        // Test price ascending
        Livewire::test(ProductList::class)
            ->set('sortBy', 'price_asc')
            ->assertViewHas('products', function ($products) {
                return $products->first()->price < $products->last()->price;
            });

        // Test price descending
        Livewire::test(ProductList::class)
            ->set('sortBy', 'price_desc')
            ->assertViewHas('products', function ($products) {
                return $products->first()->price > $products->last()->price;
            });
    }

    /** @test */
    public function it_can_sort_products_by_popularity(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'name' => 'Popular Product',
            'sales_count' => 100,
            'category_id' => $this->category->id,
        ]);
        
        Product::factory()->create([
            'status' => 'active',
            'name' => 'Less Popular Product',
            'sales_count' => 10,
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('sortBy', 'popular')
            ->assertViewHas('products', function ($products) {
                return $products->first()->sales_count > $products->last()->sales_count;
            });
    }

    /** @test */
    public function it_can_clear_all_filters(): void
    {
        Product::factory()->count(5)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->set('selectedCategories', [$this->category->id])
            ->set('minPrice', '50')
            ->set('maxPrice', '100')
            ->set('selectedRating', 4)
            ->set('onSaleOnly', true)
            ->set('stockStatus', 'in_stock')
            ->set('search', 'test')
            ->call('clearFilters')
            ->assertSet('selectedCategories', [])
            ->assertSet('minPrice', '')
            ->assertSet('maxPrice', '')
            ->assertSet('selectedRating', null)
            ->assertSet('onSaleOnly', false)
            ->assertSet('stockStatus', 'all')
            ->assertSet('search', '');
    }

    /** @test */
    public function it_correctly_counts_active_filters(): void
    {
        Product::factory()->count(3)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        $component = Livewire::test(ProductList::class);
        
        // Initially no filters
        $this->assertEquals(0, $component->get('activeFiltersCount'));
        
        // Add category filter
        $component->set('selectedCategories', [$this->category->id]);
        $this->assertEquals(1, $component->get('activeFiltersCount'));
        
        // Add price filter
        $component->set('minPrice', '50');
        $this->assertEquals(2, $component->get('activeFiltersCount'));
        
        // Add on sale filter
        $component->set('onSaleOnly', true);
        $this->assertEquals(3, $component->get('activeFiltersCount'));
    }

    /** @test */
    public function url_query_strings_are_properly_bound(): void
    {
        Product::factory()->count(3)->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);

        // Test that URL parameters are bound to component properties
        Livewire::withQueryParams([
            'minPrice' => '100',
            'maxPrice' => '500',
            'sortBy' => 'newest',
        ])
        ->test(ProductList::class)
        ->assertSet('minPrice', '100')
        ->assertSet('maxPrice', '500')
        ->assertSet('sortBy', 'newest');
    }

    /** @test */
    public function it_excludes_inactive_products(): void
    {
        // Create active product
        Product::factory()->create([
            'status' => 'active',
            'category_id' => $this->category->id,
        ]);
        
        // Create inactive product
        Product::factory()->create([
            'status' => 'inactive',
            'category_id' => $this->category->id,
        ]);

        Livewire::test(ProductList::class)
            ->assertViewHas('products', function ($products) {
                return $products->count() === 1;
            });
    }
}
