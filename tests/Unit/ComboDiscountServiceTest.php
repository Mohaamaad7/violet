<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ComboRule;
use App\Models\Category;
use App\Models\Product;
use App\Services\ComboDiscountService;

class ComboDiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_product_combo_with_multiple_quantity()
    {
        $service = new ComboDiscountService();

        $cat = Category::create(['name_en' => 'Cat', 'name_ar' => 'Cat', 'slug' => 'cat', 'is_active' => true]);

        $product = Product::create([
            'name_en' => 'Musk', 'name_ar' => 'Musk', 'slug' => 'musk',
            'price' => 100, 'category_id' => $cat->id, 'status' => 'active',
            'stock_quantity' => 10, 'sku' => '789',
        ]);

        $rule = ComboRule::create([
            'name' => 'Buy 3 Get Discount',
            'discount_percentage' => 10,
            'is_active' => true,
        ]);

        $rule->conditions()->createMany([
            ['condition_type' => 'product', 'product_id' => $product->id, 'required_quantity' => 3],
        ]);

        $items = collect([
            (object)['product' => $product, 'quantity' => 3, 'price' => 100],
        ]);

        $result = $service->calculateDiscount($items);

        // Total: 300. Discount 10% = 30.
        $this->assertNotNull($result);
        $this->assertEquals(30, $result['discount_amount']);
        $this->assertEquals($rule->id, $result['rule_id']);
    }

    public function test_calculate_discount()
    {
        $service = new ComboDiscountService();

        // Create categories
        $cat1 = Category::create(['name_en' => 'Cat 1', 'name_ar' => 'Cat 1', 'slug' => 'cat-1', 'is_active' => true]);
        $cat2 = Category::create(['name_en' => 'Cat 2', 'name_ar' => 'Cat 2', 'slug' => 'cat-2', 'is_active' => true]);

        // Create rule
        $rule = ComboRule::create([
            'name' => 'Test Rule',
            'discount_percentage' => 10,
            'is_active' => true,
        ]);

        $rule->conditions()->createMany([
            ['condition_type' => 'category', 'category_id' => $cat1->id, 'required_quantity' => 1],
            ['condition_type' => 'category', 'category_id' => $cat2->id, 'required_quantity' => 2],
        ]);

        // Mock items
        $product1 = Product::create(['name_en' => 'P1', 'name_ar' => 'P1', 'slug' => 'p-1', 'price' => 100, 'category_id' => $cat1->id, 'status' => 'active', 'stock_quantity' => 10, 'sku' => '123']);
        $product2 = Product::create(['name_en' => 'P2', 'name_ar' => 'P2', 'slug' => 'p-2', 'price' => 50, 'category_id' => $cat2->id, 'status' => 'active', 'stock_quantity' => 10, 'sku' => '456']);

        $items = collect([
            (object)['product' => $product1, 'quantity' => 1],
            (object)['product' => $product2, 'quantity' => 2],
        ]);

        $result = $service->calculateDiscount($items);

        // Subtotal: 100 + 50*2 = 200. Discount 10% = 20.
        $this->assertEquals(20, $result['discount_amount']);
        $this->assertEquals($rule->id, $result['combo_rule_id']);
    }
}
