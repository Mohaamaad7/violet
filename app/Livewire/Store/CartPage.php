<?php

namespace App\Livewire\Store;

use App\Services\CartService;
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
     * Tax rate (example - 15% VAT in Saudi Arabia)
     */
    public float $taxRate = 0.15;

    /**
     * Tax amount
     */
    public float $taxAmount = 0;

    /**
     * Total amount
     */
    public float $total = 0;

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
     * Calculate totals (shipping, tax, total)
     */
    private function calculateTotals(): void
    {
        // Calculate shipping (free if subtotal > 200 SAR)
        $this->shippingCost = $this->subtotal > 200 ? 0 : 25;

        // Calculate tax (15% VAT on subtotal + shipping)
        $this->taxAmount = ($this->subtotal + $this->shippingCost) * $this->taxRate;

        // Calculate total
        $this->total = $this->subtotal + $this->shippingCost + $this->taxAmount;
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
