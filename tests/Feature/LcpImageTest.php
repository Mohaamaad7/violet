<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LcpImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_product_card_uses_fetchpriority_high()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
            'order' => 1,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'LCP Test Product',
            'slug' => 'lcp-test-product',
            'price' => 100,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => true,
        ]);

        $response = $this->get('/');

        // The first featured product must use fetchpriority="high" (not loading="lazy")
        $response->assertSee('fetchpriority="high"', false);
        $response->assertDontSee('loading="lazy"', false);
    }
}
