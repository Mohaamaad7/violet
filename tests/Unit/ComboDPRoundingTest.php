<?php

namespace Tests\Unit;

use App\Livewire\Store\ComboLandingPage;
use Tests\TestCase;

/**
 * ComboDPRoundingTest
 *
 * Tests the proportional price distribution logic and Largest Remainder
 * Method correction to ensure ETA/ERP-compliant rounding.
 *
 * The distributeProportionally method is tested via reflection since it's
 * a private method on ComboLandingPage.
 */
class ComboDPRoundingTest extends TestCase
{
    /**
     * Helper: Invoke the private distributeProportionally method.
     */
    private function distribute(array $flatItems, float $tierPrice): array
    {
        $component = new \ReflectionClass(ComboLandingPage::class);
        $method = $component->getMethod('distributeProportionally');
        $method->setAccessible(true);

        $instance = $component->newInstanceWithoutConstructor();
        return $method->invoke($instance, $flatItems, $tierPrice);
    }

    /**
     * Test: Sum of distributed prices MUST exactly equal the tier price (no piastre leakage).
     * This is the critical ETA/ERP compliance test.
     */
    public function test_distributed_prices_sum_exactly_to_tier_price(): void
    {
        // Classic edge case: 130 EGP / 3 equal items = 43.333...
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 100.00],
            ['product_id' => 2, 'variant_id' => null, 'base_price' => 100.00],
            ['product_id' => 3, 'variant_id' => null, 'base_price' => 100.00],
        ];
        $tierPrice = 130.00;

        $result = $this->distribute($flatItems, $tierPrice);

        $sum = array_sum(array_column($result, 'price'));
        $this->assertEquals($tierPrice, round($sum, 2), 'Sum of distributed prices must exactly equal tier price');
    }

    /**
     * Test: Proportional distribution with unequal base prices.
     * A more expensive item should receive a proportionally higher share.
     */
    public function test_proportional_distribution_with_unequal_prices(): void
    {
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 200.00], // 66.67%
            ['product_id' => 2, 'variant_id' => null, 'base_price' => 100.00], // 33.33%
        ];
        $tierPrice = 250.00;

        $result = $this->distribute($flatItems, $tierPrice);

        // Item 1 (200/300 = 66.67%) should get ~166.67
        // Item 2 (100/300 = 33.33%) should get ~83.33
        $this->assertGreaterThan($result[1]['price'], $result[0]['price']);

        // Sum must be exact
        $sum = array_sum(array_column($result, 'price'));
        $this->assertEquals($tierPrice, round($sum, 2));
    }

    /**
     * Test: Original prices are correctly preserved in output.
     */
    public function test_original_prices_preserved(): void
    {
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 150.00],
            ['product_id' => 2, 'variant_id' => 5,    'base_price' => 200.00],
        ];
        $tierPrice = 300.00;

        $result = $this->distribute($flatItems, $tierPrice);

        $this->assertEquals(150.00, $result[0]['original_price']);
        $this->assertEquals(200.00, $result[1]['original_price']);
        $this->assertEquals(1, $result[0]['product_id']);
        $this->assertEquals(5, $result[1]['variant_id']);
    }

    /**
     * Test: Edge case — single item should get the full tier price.
     */
    public function test_single_item_gets_full_tier_price(): void
    {
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 100.00],
        ];
        $tierPrice = 75.00;

        $result = $this->distribute($flatItems, $tierPrice);

        $this->assertCount(1, $result);
        $this->assertEquals(75.00, $result[0]['price']);
    }

    /**
     * Test: Stress test with many items — sum must always be exact.
     */
    public function test_many_items_sum_exact(): void
    {
        $flatItems = [];
        for ($i = 1; $i <= 7; $i++) {
            $flatItems[] = [
                'product_id' => $i,
                'variant_id' => null,
                'base_price' => 100.00 + ($i * 13), // varying prices
            ];
        }
        $tierPrice = 500.00;

        $result = $this->distribute($flatItems, $tierPrice);

        $sum = array_sum(array_column($result, 'price'));
        $this->assertEquals($tierPrice, round($sum, 2), 'Sum must be exact even with 7 items');
        $this->assertCount(7, $result);
    }

    /**
     * Test: Remainder correction adds at most 0.01 per item.
     * No single item should deviate from its proportional share by more than 0.01.
     */
    public function test_remainder_correction_is_bounded(): void
    {
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 100.00],
            ['product_id' => 2, 'variant_id' => null, 'base_price' => 100.00],
            ['product_id' => 3, 'variant_id' => null, 'base_price' => 100.00],
        ];
        $tierPrice = 100.00; // 33.333... each

        $result = $this->distribute($flatItems, $tierPrice);

        foreach ($result as $item) {
            $exactProportion = $tierPrice / 3;
            $deviation = round(abs($item['price'] - round($exactProportion, 2)), 2);
            $this->assertLessThanOrEqual(0.01, $deviation, 'Each item deviation must be at most 0.01');
        }

        $sum = array_sum(array_column($result, 'price'));
        $this->assertEquals($tierPrice, round($sum, 2));
    }

    /**
     * Test: Zero base price fallback — equal split.
     */
    public function test_zero_base_price_uses_equal_split(): void
    {
        $flatItems = [
            ['product_id' => 1, 'variant_id' => null, 'base_price' => 0],
            ['product_id' => 2, 'variant_id' => null, 'base_price' => 0],
        ];
        $tierPrice = 100.00;

        $result = $this->distribute($flatItems, $tierPrice);

        $this->assertEquals(50.00, $result[0]['price']);
        $this->assertEquals(50.00, $result[1]['price']);
    }
}
