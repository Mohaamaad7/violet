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

    public function test_dp_algorithm_calculates_optimal_tiers()
    {
        $service = new ComboDiscountService();
        $cat = Category::create(['name_en' => 'Cat', 'name_ar' => 'Cat', 'slug' => 'cat', 'is_active' => true]);
        
        $product = Product::create([
            'name_en' => 'P1', 'name_ar' => 'P1', 'slug' => 'p1',
            'price' => 50, 'category_id' => $cat->id, 'status' => 'active', 'stock_quantity' => 10, 'sku' => '123'
        ]);

        $rule = ComboRule::create([
            'name' => 'Volume Pricing',
            'is_active' => true,
            'tiers' => [
                ['quantity' => 3, 'discount_type' => 'fixed_price', 'fixed_price' => 120], // 40 per unit
                ['quantity' => 2, 'discount_type' => 'fixed_price', 'fixed_price' => 90],  // 45 per unit
            ],
        ]);

        $rule->conditions()->create([
            'condition_type' => 'category', 'category_id' => $cat->id
        ]);

        // Cart with 5 items (should use 3 + 2 tiers = 120 + 90 = 210)
        // Original price = 50 * 5 = 250
        $items = [
            (object)['id' => 1, 'product_id' => $product->id, 'quantity' => 5, 'price' => 50, 'product_variant_id' => null]
        ];

        $result = $service->calculateDiscount($items);
        
        $this->assertNotNull($result);
        $this->assertEquals(40, $result['discount_amount']); // 250 - 210
        
        // Cart with 4 items (should use 2 + 2 = 180)
        // Original price = 200
        $items4 = [
            (object)['id' => 2, 'product_id' => $product->id, 'quantity' => 4, 'price' => 50, 'product_variant_id' => null]
        ];

        $result4 = $service->calculateDiscount($items4);
        $this->assertEquals(20, $result4['discount_amount']); // 200 - 180
    }

    public function test_proportional_distribution_and_rounding_remainder()
    {
        $service = new ComboDiscountService();
        $cat = Category::create(['name_en' => 'Cat', 'name_ar' => 'Cat', 'slug' => 'cat', 'is_active' => true]);
        
        $product = Product::create([
            'name_en' => 'P1', 'name_ar' => 'P1', 'slug' => 'p1',
            'price' => 60, 'category_id' => $cat->id, 'status' => 'active', 'stock_quantity' => 10, 'sku' => '123'
        ]);

        $rule = ComboRule::create([
            'name' => 'Rounding Test',
            'is_active' => true,
            'tiers' => [
                ['quantity' => 3, 'discount_type' => 'fixed_price', 'fixed_price' => 130], // 130 / 3 = 43.333...
            ],
        ]);

        $rule->conditions()->create([
            'condition_type' => 'category', 'category_id' => $cat->id
        ]);

        $items = [
            (object)['id' => 1, 'product_id' => $product->id, 'quantity' => 3, 'price' => 60, 'product_variant_id' => null]
        ];

        $result = $service->calculateDiscount($items);
        
        $this->assertNotNull($result);
        $this->assertEquals(50, $result['discount_amount']); // 180 - 130 = 50
        
        $adjustedItems = $result['adjusted_items'];
        $this->assertCount(3, $adjustedItems);
        
        // Ensure the sum of the final prices strictly matches the tier price (130)
        $sum = array_sum(array_column($adjustedItems, 'final_price'));
        $this->assertEquals(130.00, $sum);
        
        // Check exact distribution (Largest Remainder should put 0.01 on the first item)
        // 130 / 3 = 43.33. Distributed = 129.99. Diff = 0.01
        $this->assertEquals(43.34, $adjustedItems[0]['final_price']);
        $this->assertEquals(43.33, $adjustedItems[1]['final_price']);
        $this->assertEquals(43.33, $adjustedItems[2]['final_price']);
    }
}
