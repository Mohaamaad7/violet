<?php

namespace App\Livewire\Store;

use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * CartPage - Full Shopping Cart Page
 * 
 * Displays complete cart with detailed item management.
 * Provides quantity adjustment, remove, clear, and checkout options.
 * 
 * @package App\Livewire\Store
 */
class CartPage extends Component
{
    /**
     * Cart items count
     */
    public int $cartCount = 0;

    /**
     * Cart subtotal
     */
    public float $subtotal = 0;

    /**
     * Shipping cost (example - can be dynamic)
     */
    public float $shippingCost = 0;

    /**
     * Tax rate
     */
    public float $taxRate = 0;

    /**
     * Tax amount
     */
    public float $taxAmount = 0;

    /**
     * Total amount
     */
    public float $total = 0;

    // Shipping discount state (for progress bar display)
    public bool  $discountEnabled    = false;
    public float $discountThreshold  = 0;
    public float $discountPercentage = 0;

    /**
     * Inject CartService
     */
    protected CartService $cartService;

    /**
     * Boot method to inject CartService
     */
    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    /**
     * Mount component
     */
    public function mount(): void
    {
        $this->loadCartData();
        $this->calculateTotals();
    }

    /**
     * Listen to cart-updated event and refresh
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $this->loadCartData();
        $this->calculateTotals();
        $this->dispatch('cart-count-updated', count: $this->cartCount);
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        $result = $this->cartService->updateQuantity($cartItemId, $quantity);

        if ($result['success']) {
            $this->loadCartData();
            $this->calculateTotals();
            $this->dispatch('cart-count-updated', count: $this->cartCount);
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'success',
            ]);
        } else {
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'error',
            ]);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $cartItemId): void
    {
        $result = $this->cartService->removeItem($cartItemId);

        if ($result['success']) {
            $this->loadCartData();
            $this->calculateTotals();
            $this->dispatch('cart-count-updated', count: $this->cartCount);
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'success',
            ]);
        } else {
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'error',
            ]);
        }
    }

    /**
     * Clear entire cart
     */
    public function clearCart(): void
    {
        $result = $this->cartService->clearCart();

        if ($result['success']) {
            $this->loadCartData();
            $this->calculateTotals();
            $this->dispatch('cart-count-updated', count: $this->cartCount);
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'success',
            ]);
        }
    }

    /**
     * Load cart data from service
     */
    private function loadCartData(): void
    {
        $this->cartCount = $this->cartService->getCartCount();
        $this->subtotal = $this->cartService->getSubtotal();
    }

    /**
     * Load shipping discount config using Eloquent + Cache (no Setting::get()).
     */
    private function getShippingDiscountConfig(): array
    {
        return Cache::remember('shipping_discount_config', 600, function () {
            return [
                'enabled'    => (bool)  Setting::where('key', 'shipping_discount_enabled')->value('value'),
                'threshold'  => (float) Setting::where('key', 'shipping_discount_threshold')->value('value'),
                'percentage' => (float) Setting::where('key', 'shipping_discount_percentage')->value('value'),
            ];
        });
    }

    /**
     * Computed property: shipping discount progress for the cart progress bar.
     */
    public function getDiscountProgressProperty(): array
    {
        if (!$this->discountEnabled || $this->discountThreshold <= 0 || $this->subtotal <= 0) {
            return ['show' => false];
        }
        $remaining = max(0, $this->discountThreshold - $this->subtotal);
        return [
            'show'       => true,
            'percentage' => $this->discountPercentage,
            'threshold'  => $this->discountThreshold,
            'remaining'  => $remaining,
            'progress'   => min(100, round(($this->subtotal / $this->discountThreshold) * 100, 1)),
            'achieved'   => $remaining <= 0,
        ];
    }

    /**
     * Calculate totals — shipping is NOT calculated on cart page (no address yet).
     * Only loads discount config for the progress bar display.
     */
    private function calculateTotals(): void
    {
        // Edge case: empty cart
        if ($this->subtotal <= 0) {
            $this->shippingCost = 0;
            $this->taxAmount    = 0;
            $this->total        = 0;
            $this->discountEnabled = false;
            return;
        }

        // Shipping is unknown before address selection — shown as 0 with disclaimer
        $this->shippingCost = 0;
        $this->taxAmount    = 0;
        $this->total        = $this->subtotal;

        // Load discount config for the progress bar (read-only, no calculation)
        $config = $this->getShippingDiscountConfig();
        $this->discountEnabled    = $config['enabled'];
        $this->discountThreshold  = $config['threshold'];
        $this->discountPercentage = $config['percentage'];
    }

    /**
     * Render component
     */
    public function render()
    {
        $cart = $this->cartService->getCart();

        return view('livewire.store.cart-page', [
            'cart' => $cart,
        ])->layout('layouts.store');
    }
}
