<?php

namespace App\Services;

use App\Models\ComboRule;
use App\Models\ComboRuleUsage;
use App\Models\Product;
use Illuminate\Support\Collection;

class ComboDiscountService
{
    /**
     * Calculate the best combo discount for a given set of cart items.
     * 
     * @param Collection|array $cartItems Can be CartItem models or arrays
     * @param int|null $customerId
     * @return array|null Returns ['rule_id', 'rule_name', 'discount_amount', 'adjusted_items'] or null
     */
    public function calculateDiscount($cartItems, ?int $customerId = null): ?array
    {
        $rules = ComboRule::with('conditions')->active()->ordered()->get();

        if ($rules->isEmpty()) {
            return null;
        }

        $items = $this->normalizeCartItems($cartItems);
        
        if (empty($items)) {
            return null;
        }

        foreach ($rules as $rule) {
            // Check usage limit
            if ($rule->max_uses_per_user !== null && $customerId !== null) {
                $usageCount = ComboRuleUsage::where('combo_rule_id', $rule->id)
                    ->where('customer_id', $customerId)
                    ->count();

                if ($usageCount >= $rule->max_uses_per_user) {
                    continue;
                }
            }

            $result = $this->evaluateRule($rule, $items);

            if ($result && $result['discount_amount'] > 0) {
                return [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'discount_amount' => round($result['discount_amount'], 2),
                    'adjusted_items' => $result['adjusted_items'],
                ];
            }
        }

        return null;
    }

    public function recordUsage(int $ruleId, int $orderId, ?int $customerId): void
    {
        ComboRuleUsage::create([
            'combo_rule_id' => $ruleId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
        ]);
    }

    public function decrementUsage(int $ruleId, int $orderId): void
    {
        ComboRuleUsage::where('combo_rule_id', $ruleId)
            ->where('order_id', $orderId)
            ->delete();
    }

    private function normalizeCartItems($cartItems): array
    {
        $normalized = [];
        
        foreach ($cartItems as $item) {
            $productId = is_array($item) ? $item['product_id'] : $item->product_id;
            $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
            $price = is_array($item) ? $item['price'] : $item->price;
            $cartItemId = is_array($item) ? ($item['id'] ?? null) : $item->id;
            $variantId = is_array($item) ? ($item['product_variant_id'] ?? null) : $item->product_variant_id;

            $product = Product::find($productId);
            if (!$product) continue;

            for ($i = 0; $i < $quantity; $i++) {
                $normalized[] = [
                    'unique_id' => uniqid('item_', true),
                    'cart_item_id' => $cartItemId,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'category_id' => $product->category_id,
                    'price' => (float) $price,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Evaluate rule using DP Unbounded Knapsack
     */
    private function evaluateRule(ComboRule $rule, array $allItems): ?array
    {
        if ($rule->conditions->isEmpty()) {
            return null;
        }

        // 1. Find eligible items
        $eligibleItems = [];
        $ineligibleItems = [];
        foreach ($allItems as $item) {
            $matches = false;
            foreach ($rule->conditions as $condition) {
                if ($condition->condition_type === 'product' && $item['product_id'] == $condition->product_id) {
                    $matches = true;
                    break;
                } elseif ($condition->condition_type === 'category' && $item['category_id'] == $condition->category_id) {
                    $matches = true;
                    break;
                }
            }
            if ($matches) {
                $eligibleItems[] = $item;
            } else {
                $ineligibleItems[] = $item;
            }
        }

        if (empty($eligibleItems)) {
            return null;
        }

        // 2. Sort eligible items DESCENDING by price
        usort($eligibleItems, function($a, $b) {
            return $b['price'] <=> $a['price'];
        });

        // Parse tiers
        $tiers = is_array($rule->tiers) ? $rule->tiers : [];
        if (empty($tiers) && $rule->discount_type) {
            // Fallback for legacy data not migrated
            $qty = $rule->conditions->first()->required_quantity ?? 1;
            $tiers = [[
                'quantity' => $qty,
                'discount_type' => $rule->discount_type,
                'discount_percentage' => $rule->discount_percentage,
                'fixed_price' => $rule->fixed_price,
            ]];
        }

        if (empty($tiers)) {
            return null;
        }

        // 3. DP Setup
        $n = count($eligibleItems);
        $dp = array_fill(0, $n + 1, PHP_FLOAT_MAX);
        $dp[0] = 0;
        $choices = array_fill(0, $n + 1, null);

        for ($i = 1; $i <= $n; $i++) {
            // Option A: Item $i falls outside tiers (pays base price)
            $cost = $dp[$i - 1] + $eligibleItems[$i - 1]['price'];
            if ($cost < $dp[$i]) {
                $dp[$i] = $cost;
                $choices[$i] = ['type' => 'base', 'count' => 1];
            }

            // Option B: Apply Tiers
            foreach ($tiers as $tier) {
                $q = (int) ($tier['quantity'] ?? 1);
                if ($i >= $q) {
                    // Calculate cost of this tier chunk
                    $chunkBasePrice = 0;
                    for ($j = $i - $q; $j < $i; $j++) {
                        $chunkBasePrice += $eligibleItems[$j]['price'];
                    }

                    $tierPrice = $chunkBasePrice;
                    if (($tier['discount_type'] ?? 'percentage') === 'fixed_price') {
                        $tierPrice = (float) ($tier['fixed_price'] ?? 0);
                    } else {
                        $pct = (float) ($tier['discount_percentage'] ?? 0);
                        $tierPrice = $chunkBasePrice * (1 - $pct / 100);
                    }

                    $cost = $dp[$i - $q] + $tierPrice;
                    if ($cost < $dp[$i]) {
                        $dp[$i] = $cost;
                        $choices[$i] = [
                            'type' => 'tier', 
                            'count' => $q, 
                            'tier_price' => $tierPrice
                        ];
                    }
                }
            }
        }

        // Original total without discounts
        $originalTotal = array_sum(array_column($eligibleItems, 'price'));
        $discountAmount = $originalTotal - $dp[$n];

        if ($discountAmount <= 0) {
            return null; // No actual savings
        }

        // 4. Backtrack choices and apply proportional distribution
        $assignedPrices = [];
        $curr = $n;
        while ($curr > 0) {
            $choice = $choices[$curr];
            
            if ($choice['type'] === 'base') {
                $assignedPrices[] = [
                    'unique_id' => $eligibleItems[$curr - 1]['unique_id'],
                    'cart_item_id' => $eligibleItems[$curr - 1]['cart_item_id'],
                    'product_id' => $eligibleItems[$curr - 1]['product_id'],
                    'product_variant_id' => $eligibleItems[$curr - 1]['product_variant_id'],
                    'original_price' => $eligibleItems[$curr - 1]['price'],
                    'final_price' => $eligibleItems[$curr - 1]['price'],
                    'is_combo_discounted' => false,
                ];
                $curr -= 1;
            } else {
                $q = $choice['count'];
                $tierPrice = $choice['tier_price'];
                
                $chunkItems = [];
                $sumBase = 0;
                for ($j = $curr - $q; $j < $curr; $j++) {
                    $chunkItems[] = $eligibleItems[$j];
                    $sumBase += $eligibleItems[$j]['price'];
                }
                
                // Proportional Price Distribution
                $unitPrices = [];
                $distributed = 0;
                foreach ($chunkItems as $idx => $cItem) {
                    $prop = $sumBase > 0 
                        ? round($tierPrice * ($cItem['price'] / $sumBase), 2)
                        : round($tierPrice / $q, 2);
                    $unitPrices[$idx] = $prop;
                    $distributed += $prop;
                }
                
                // Largest Remainder Correction (Add difference to the first, most expensive item)
                $diff = round($tierPrice - $distributed, 2);
                if ($diff != 0) {
                    $unitPrices[0] += $diff;
                }
                
                foreach ($chunkItems as $idx => $cItem) {
                    $assignedPrices[] = [
                        'unique_id' => $cItem['unique_id'],
                        'cart_item_id' => $cItem['cart_item_id'],
                        'product_id' => $cItem['product_id'],
                        'product_variant_id' => $cItem['product_variant_id'],
                        'original_price' => $cItem['price'],
                        'final_price' => round($unitPrices[$idx], 2),
                        'is_combo_discounted' => true,
                    ];
                }
                $curr -= $q;
            }
        }

        // Add ineligible items back
        foreach ($ineligibleItems as $item) {
            $assignedPrices[] = [
                'unique_id' => $item['unique_id'],
                'cart_item_id' => $item['cart_item_id'],
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'original_price' => $item['price'],
                'final_price' => $item['price'],
                'is_combo_discounted' => false,
            ];
        }

        return [
            'discount_amount' => $discountAmount,
            'adjusted_items' => $assignedPrices,
        ];
    }
}
