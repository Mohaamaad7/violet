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
     * @param Collection|array $cartItems  Can be CartItem models or arrays with product_id, quantity, price
     * @param int|null $customerId
     * @return array|null Returns ['rule_id' => int, 'discount_amount' => float, 'rule_name' => string] or null
     */
    public function calculateDiscount($cartItems, ?int $customerId = null): ?array
    {
        $rules = ComboRule::with('conditions')->active()->ordered()->get();

        if ($rules->isEmpty()) {
            return null;
        }

        // Normalize cart items into a standard structure with category info
        $items = $this->normalizeCartItems($cartItems);
        
        if (empty($items)) {
            return null;
        }

        foreach ($rules as $rule) {
            // Check usage limit if max_uses_per_user is set
            if ($rule->max_uses_per_user !== null && $customerId !== null) {
                $usageCount = ComboRuleUsage::where('combo_rule_id', $rule->id)
                    ->where('customer_id', $customerId)
                    ->count();

                if ($usageCount >= $rule->max_uses_per_user) {
                    continue; // Skip this rule, limit reached
                }
            }

            $discountAmount = $this->evaluateRule($rule, $items);

            if ($discountAmount > 0) {
                return [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'discount_amount' => round($discountAmount, 2),
                ];
            }
        }

        return null;
    }

    /**
     * Record usage of a combo rule.
     */
    public function recordUsage(int $ruleId, int $orderId, ?int $customerId): void
    {
        ComboRuleUsage::create([
            'combo_rule_id' => $ruleId,
            'order_id' => $orderId,
            'customer_id' => $customerId,
        ]);
    }

    /**
     * Decrement usage (e.g., upon return).
     */
    public function decrementUsage(int $ruleId, int $orderId): void
    {
        ComboRuleUsage::where('combo_rule_id', $ruleId)
            ->where('order_id', $orderId)
            ->delete();
    }

    /**
     * Normalize cart items to a simple array with category_id
     */
    private function normalizeCartItems($cartItems): array
    {
        $normalized = [];
        
        foreach ($cartItems as $item) {
            $productId = is_array($item) ? $item['product_id'] : $item->product_id;
            $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
            $price = is_array($item) ? $item['price'] : $item->price;

            // Cache or load product category
            $product = Product::find($productId);
            if (!$product) continue;

            // We explode quantity into individual units for easier "cheapest first" sorting and picking
            for ($i = 0; $i < $quantity; $i++) {
                $normalized[] = [
                    'product_id' => $productId,
                    'category_id' => $product->category_id,
                    'price' => (float) $price,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Evaluate a single rule against normalized items
     */
    private function evaluateRule(ComboRule $rule, array $items): float
    {
        if ($rule->conditions->isEmpty()) {
            return 0; // Invalid rule
        }

        $applicableItems = [];

        // Check each condition
        foreach ($rule->conditions as $condition) {
            $requiredQuantity = $condition->required_quantity;
            $categoryId = $condition->category_id;

            // Find all items matching the category, that haven't been used yet (we simulate this by picking)
            // Wait, items might overlap if categories are the same? Typically conditions have distinct categories.
            // Let's filter items for this category
            $categoryItems = array_filter($items, function($item) use ($categoryId) {
                return $item['category_id'] == $categoryId;
            });

            if (count($categoryItems) < $requiredQuantity) {
                return 0; // Condition not met, whole rule fails
            }

            // Sort by price ascending (cheapest first)
            usort($categoryItems, function($a, $b) {
                return $a['price'] <=> $b['price'];
            });

            // Take the required quantity
            $pickedItems = array_slice($categoryItems, 0, $requiredQuantity);
            
            // Add to applicable items
            $applicableItems = array_merge($applicableItems, $pickedItems);

            // Remove picked items from the main pool so they aren't reused for another condition (edge case)
            foreach ($pickedItems as $picked) {
                $key = array_search($picked, $items);
                if ($key !== false) {
                    unset($items[$key]);
                }
            }
        }

        // All conditions met!
        $totalApplicablePrice = array_sum(array_column($applicableItems, 'price'));
        
        return $totalApplicablePrice * ($rule->discount_percentage / 100);
    }
}
