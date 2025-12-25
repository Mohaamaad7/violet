<?php

namespace App\Services;

use App\Models\CodeUsage;
use App\Models\DiscountCode;
use App\Models\Order;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Validate a coupon code
     * 
     * @param string $code The coupon code to validate
     * @param array $cartItems Array of cart items with product_id, category_id, price, quantity
     * @param int|null $customerId Customer ID (null for guests)
     * @return array ['valid' => bool, 'error' => string|null, 'coupon' => DiscountCode|null]
     */
    public function validateCoupon(string $code, array $cartItems, ?int $customerId = null): array
    {
        // Find the coupon
        $coupon = DiscountCode::where('code', $code)->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.invalid'),
                'coupon' => null,
            ];
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.invalid'),
                'coupon' => null,
            ];
        }

        // Check start date
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.not_started'),
                'coupon' => null,
            ];
        }

        // Check expiry date
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.expired'),
                'coupon' => null,
            ];
        }

        // Check total usage limit
        if ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.usage_limit_reached'),
                'coupon' => null,
            ];
        }

        // Check per-customer usage limit
        if ($customerId !== null && $coupon->usage_limit_per_user !== null) {
            $customerUsageCount = $coupon->usages()
                ->where('user_id', $customerId)
                ->count();

            if ($customerUsageCount >= $coupon->usage_limit_per_user) {
                return [
                    'valid' => false,
                    'error' => __('messages.coupon_errors.already_used'),
                    'coupon' => null,
                ];
            }
        }

        // Calculate applicable subtotal
        $applicableSubtotal = $this->calculateApplicableSubtotal($coupon, $cartItems);

        // Check minimum order amount
        if (!$coupon->meetsMinOrderAmount($applicableSubtotal)) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.min_order_not_met', [
                    'amount' => number_format($coupon->min_order_amount, 2) . ' ' . __('messages.currency.egp')
                ]),
                'coupon' => null,
            ];
        }

        // Check if coupon applies to any cart items
        if ($applicableSubtotal <= 0 && !$coupon->isFreeShipping()) {
            return [
                'valid' => false,
                'error' => __('messages.coupon_errors.not_applicable'),
                'coupon' => null,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'coupon' => $coupon,
        ];
    }

    /**
     * Calculate the discount amount for a coupon
     * 
     * @param DiscountCode $coupon The coupon to calculate
     * @param array $cartItems Array of cart items
     * @param float $shippingCost Current shipping cost
     * @return array ['discount' => float, 'shipping_discount' => float, 'applicable_items' => array]
     */
    public function calculateDiscount(DiscountCode $coupon, array $cartItems, float $shippingCost = 0): array
    {
        $discount = 0;
        $shippingDiscount = 0;
        $applicableItems = [];

        // Free shipping coupon
        if ($coupon->isFreeShipping()) {
            return [
                'discount' => 0,
                'shipping_discount' => $shippingCost,
                'applicable_items' => [],
            ];
        }

        // Calculate applicable items and their subtotal
        $applicableSubtotal = 0;
        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? 0;
            $categoryId = $item['category_id'] ?? null;
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            if ($coupon->isProductApplicable($productId, $categoryId)) {
                $itemSubtotal = $price * $quantity;
                $applicableSubtotal += $itemSubtotal;
                $applicableItems[] = [
                    'product_id' => $productId,
                    'subtotal' => $itemSubtotal,
                ];
            }
        }

        // Calculate discount based on type
        if ($coupon->isPercentage()) {
            $discount = $applicableSubtotal * ($coupon->discount_value / 100);

            // Apply max discount cap
            if ($coupon->max_discount_amount !== null && $discount > $coupon->max_discount_amount) {
                $discount = (float) $coupon->max_discount_amount;
            }
        } elseif ($coupon->isFixed()) {
            $discount = (float) $coupon->discount_value;

            // Fixed discount cannot exceed applicable subtotal
            if ($discount > $applicableSubtotal) {
                $discount = $applicableSubtotal;
            }
        }

        return [
            'discount' => round($discount, 2),
            'shipping_discount' => $shippingDiscount,
            'applicable_items' => $applicableItems,
        ];
    }

    /**
     * Apply coupon to an order (record usage, increment counter)
     * 
     * @param DiscountCode $coupon The coupon being used
     * @param Order $order The order it's being applied to
     * @param int|null $customerId Customer ID (null for guests)
     * @param float $discountAmount The discount amount applied
     */
    public function applyCoupon(DiscountCode $coupon, Order $order, ?int $customerId, float $discountAmount): void
    {
        // Increment usage counter
        $coupon->increment('times_used');

        // Record usage in code_usages table
        CodeUsage::create([
            'discount_code_id' => $coupon->id,
            'user_id' => $customerId,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
        ]);
    }

    /**
     * Generate a random coupon code
     * 
     * @param int $length Length of the code
     * @param string $prefix Optional prefix
     * @return string
     */
    public function generateRandomCode(int $length = 8, string $prefix = ''): string
    {
        do {
            $code = $prefix . strtoupper(Str::random($length));
        } while (DiscountCode::where('code', $code)->exists());

        return $code;
    }

    /**
     * Calculate the subtotal of applicable items only
     */
    private function calculateApplicableSubtotal(DiscountCode $coupon, array $cartItems): float
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? 0;
            $categoryId = $item['category_id'] ?? null;
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            if ($coupon->isProductApplicable($productId, $categoryId)) {
                $subtotal += $price * $quantity;
            }
        }

        return $subtotal;
    }
}
