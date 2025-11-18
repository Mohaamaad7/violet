<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * CartService - Hybrid Shopping Cart System
 * 
 * Handles all cart operations with Database Persistence for both Guests and Users.
 * Uses UUID-based session_id stored in long-lived cookie for guest identification.
 * 
 * @package App\Services
 */
class CartService
{
    /**
     * Cookie name for guest cart session
     */
    private const CART_COOKIE_NAME = 'cart_session_id';
    
    /**
     * Cookie lifetime in minutes (30 days)
     */
    private const COOKIE_LIFETIME = 43200; // 30 days * 24 hours * 60 minutes

    /**
     * Get or create cart session identifier for the current user/guest
     * 
     * @return string UUID session identifier
     */
    public function getCartSessionId(): string
    {
        // If user is authenticated, use their user_id
        if (Auth::check()) {
            return 'user_' . Auth::id();
        }

        // For guests, get or create cart_session_id from cookie
        $sessionId = Cookie::get(self::CART_COOKIE_NAME);

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            
            // Queue cookie to be sent with the response
            Cookie::queue(
                self::CART_COOKIE_NAME,
                $sessionId,
                self::COOKIE_LIFETIME,
                '/',
                null,
                false, // secure (set to true in production with HTTPS)
                true,  // httpOnly
                false, // raw
                'lax'  // sameSite
            );
        }

        return $sessionId;
    }

    /**
     * Get the active cart for current user/guest
     * 
     * @return Cart|null
     */
    public function getCart(): ?Cart
    {
        $sessionId = $this->getCartSessionId();

        if (Auth::check()) {
            return Cart::with(['items.product.media'])
                ->where('user_id', Auth::id())
                ->first();
        }

        return Cart::with(['items.product.media'])
            ->where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();
    }

    /**
     * Get or create cart for current user/guest
     * 
     * @return Cart
     */
    public function getOrCreateCart(): Cart
    {
        $cart = $this->getCart();

        if ($cart) {
            return $cart;
        }

        $sessionId = $this->getCartSessionId();

        return Cart::create([
            'user_id' => Auth::id(),
            'session_id' => Auth::check() ? null : $sessionId,
        ]);
    }

    /**
     * Add product to cart with stock validation
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return array ['success' => bool, 'message' => string, 'cart' => Cart|null]
     */
    public function addToCart(int $productId, int $quantity = 1, ?int $variantId = null): array
    {
        // Load product with necessary relationships
        $product = Product::with(['variants', 'media'])->find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'المنتج غير موجود',
                'cart' => null,
            ];
        }

        // Validate stock
        $maxStock = $this->getProductMaxStock($product, $variantId);
        
        if ($maxStock <= 0) {
            return [
                'success' => false,
                'message' => 'المنتج غير متوفر حالياً',
                'cart' => null,
            ];
        }

        $cart = $this->getOrCreateCart();

        // Check if item already exists in cart
        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem->quantity + $quantity;

            // Validate against stock
            if ($newQuantity > $maxStock) {
                return [
                    'success' => false,
                    'message' => "الحد الأقصى المتاح: {$maxStock} قطعة",
                    'cart' => null,
                ];
            }

            $existingItem->update([
                'quantity' => $newQuantity,
                'price' => $product->sale_price ?? $product->price,
            ]);

            return [
                'success' => true,
                'message' => 'تم تحديث الكمية في السلة',
                'cart' => $cart->load('items.product.media'),
            ];
        }

        // Validate quantity
        if ($quantity > $maxStock) {
            return [
                'success' => false,
                'message' => "الحد الأقصى المتاح: {$maxStock} قطعة",
                'cart' => null,
            ];
        }

        // Add new item
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $product->sale_price ?? $product->price,
        ]);

        return [
            'success' => true,
            'message' => 'تمت إضافة المنتج للسلة',
            'cart' => $cart->load('items.product.media'),
        ];
    }

    /**
     * Update cart item quantity
     * 
     * @param int $cartItemId
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateQuantity(int $cartItemId, int $quantity): array
    {
        $cart = $this->getCart();

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'السلة غير موجودة',
            ];
        }

        $cartItem = $cart->items()->find($cartItemId);

        if (!$cartItem) {
            return [
                'success' => false,
                'message' => 'المنتج غير موجود في السلة',
            ];
        }

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        // Validate stock
        $product = $cartItem->product;
        $maxStock = $this->getProductMaxStock($product, $cartItem->variant_id);

        if ($quantity > $maxStock) {
            return [
                'success' => false,
                'message' => "الحد الأقصى المتاح: {$maxStock} قطعة",
            ];
        }

        $cartItem->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => 'تم تحديث الكمية',
        ];
    }

    /**
     * Remove item from cart
     * 
     * @param int $cartItemId
     * @return array ['success' => bool, 'message' => string]
     */
    public function removeItem(int $cartItemId): array
    {
        $cart = $this->getCart();

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'السلة غير موجودة',
            ];
        }

        $cartItem = $cart->items()->find($cartItemId);

        if (!$cartItem) {
            return [
                'success' => false,
                'message' => 'المنتج غير موجود في السلة',
            ];
        }

        $cartItem->delete();

        // If cart is empty, optionally delete the cart
        if ($cart->items()->count() === 0) {
            $cart->delete();
        }

        return [
            'success' => true,
            'message' => 'تم إزالة المنتج من السلة',
        ];
    }

    /**
     * Clear entire cart
     * 
     * @return array ['success' => bool, 'message' => string]
     */
    public function clearCart(): array
    {
        $cart = $this->getCart();

        if (!$cart) {
            return [
                'success' => true,
                'message' => 'السلة فارغة بالفعل',
            ];
        }

        $cart->items()->delete();
        $cart->delete();

        return [
            'success' => true,
            'message' => 'تم تفريغ السلة',
        ];
    }

    /**
     * Get cart items count
     * 
     * @return int
     */
    public function getCartCount(): int
    {
        $cart = $this->getCart();

        if (!$cart) {
            return 0;
        }

        return $cart->items()->sum('quantity');
    }

    /**
     * Get cart subtotal (before shipping and discounts)
     * 
     * @return float
     */
    public function getSubtotal(): float
    {
        $cart = $this->getCart();

        if (!$cart) {
            return 0.0;
        }

        return $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Merge guest cart into user cart after login
     * 
     * @param string $guestSessionId
     * @param int $userId
     * @return void
     */
    public function mergeGuestCart(string $guestSessionId, int $userId): void
    {
        // Find guest cart
        $guestCart = Cart::where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (!$guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        // Find or create user cart
        $userCart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['session_id' => null]
        );

        // Merge items
        foreach ($guestCart->items as $guestItem) {
            // Check if user already has this product in cart
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existingItem) {
                // Update quantity (respecting stock limits)
                $product = $guestItem->product;
                $maxStock = $this->getProductMaxStock($product, $guestItem->variant_id);
                $newQuantity = min($existingItem->quantity + $guestItem->quantity, $maxStock);

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'price' => $guestItem->price, // Use latest price
                ]);
            } else {
                // Move item to user cart
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        // Delete guest cart
        $guestCart->delete();

        // Clear the guest cookie
        Cookie::queue(Cookie::forget(self::CART_COOKIE_NAME));
    }

    /**
     * Get maximum available stock for a product (considering variants)
     * 
     * @param Product $product
     * @param int|null $variantId
     * @return int
     */
    private function getProductMaxStock(Product $product, ?int $variantId = null): int
    {
        if ($variantId) {
            $variant = $product->variants()->find($variantId);
            return $variant ? $variant->stock : 0;
        }

        return $product->stock ?? 0;
    }

    /**
     * Check if product is available with requested quantity
     * 
     * @param int $productId
     * @param int $quantity
     * @param int|null $variantId
     * @return bool
     */
    public function isAvailable(int $productId, int $quantity, ?int $variantId = null): bool
    {
        $product = Product::with('variants')->find($productId);

        if (!$product) {
            return false;
        }

        $maxStock = $this->getProductMaxStock($product, $variantId);

        return $quantity <= $maxStock;
    }
}
