<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
            'sku' => 'LCP-TEST-001',
            'price' => 100,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => true,
        ]);

        $response = $this->get('/');

        // The first featured product must use fetchpriority="high" (not loading="lazy")
        $response->assertSee('fetchpriority="high"', false);
        // The same product may also appear lazily in New Arrivals below the fold.
        preg_match('/<img\b[^>]*alt="LCP Test Product"[^>]*>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringContainsString('fetchpriority="high"', $matches[0]);
        $this->assertStringNotContainsString('loading="lazy"', $matches[0]);
    }
}
