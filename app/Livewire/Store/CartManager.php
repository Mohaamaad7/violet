<?php

namespace App\Livewire\Store;

use App\Services\CartService;
use Livewire\Component;
use Livewire\Attributes\On;

/**
 * CartManager - Slide-over Mini Cart Component
 * 
 * Displays a slide-over panel with cart items summary.
 * Updates in real-time when cart is modified.
 * 
 * @package App\Livewire\Store
 */
class CartManager extends Component
{
    /**
     * Whether the cart panel is open
     */
    public bool $isOpen = false;

    /**
     * Cart items count
     */
    public int $cartCount = 0;

    /**
     * Cart subtotal
     */
    public float $subtotal = 0;

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
        try {
            $this->loadCartData();
        } catch (\Exception $e) {
            // Log error but don't break the component
            logger()->error('CartManager mount failed: ' . $e->getMessage());
            $this->cartCount = 0;
            $this->subtotal = 0;
        }
    }

    /**
     * Open cart panel
     */
    public function openCart(): void
    {
        $this->isOpen = true;
        $this->loadCartData();
    }

    /**
     * Close cart panel
     */
    public function closeCart(): void
    {
        $this->isOpen = false;
    }

    /**
     * Listen to cart-updated event and refresh
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $this->loadCartData();
        $this->dispatch('cart-count-updated', count: $this->cartCount);
    }

    /**
     * Listen to add-to-cart event from product cards and add via service
     */
    #[On('add-to-cart')]
    public function addToCart(int $productId, int $quantity = 1): void
    {
        $result = $this->cartService->addToCart($productId, $quantity);

        if ($result['success']) {
            $this->loadCartData();
            $this->dispatch('cart-count-updated', count: $this->cartCount);
            $this->dispatch('cart-updated');
            $this->dispatch('show-toast', [
                'message' => $result['message'],
                'type' => 'success',
            ]);

            $this->openCart();
        } else {
            $this->dispatch('show-toast', [
                'message' => $result['message'] ?? 'تعذر إضافة المنتج للسلة',
                'type' => 'error',
            ]);
        }
    }

    /**
     * Listen to open-cart event
     */
    #[On('open-cart')]
    public function openCartFromEvent(): void
    {
        $this->openCart();
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        $result = $this->cartService->updateQuantity($cartItemId, $quantity);

        if ($result['success']) {
            $this->loadCartData();
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
     * Render component
     */
    public function render()
    {
        try {
            $cart = $this->cartService->getCart();
        } catch (\Exception $e) {
            logger()->error('CartManager render failed: ' . $e->getMessage());
            $cart = null;
        }

        return view('livewire.store.cart-manager', [
            'cart' => $cart,
        ]);
    }
}
