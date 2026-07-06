<?php

namespace App\Livewire\Store;

use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Combo savings: aggregated from locked proportional prices.
     * Calculated as sum((original_price - price) * quantity) for combo items.
     */
    public float $comboSavings = 0;

    /**
     * Data for the combo delete confirmation modal.
     * Contains the UUID and sibling product names.
     */
    public array $comboDeleteModal = [
        'show' => false,
        'cartItemId' => null,
        'comboUuid' => null,
        'siblingNames' => [],
    ];

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
     * Initiate combo item removal — shows dynamic confirmation modal.
     * For non-combo items, removes directly.
     */
    public function confirmRemoveItem(int $cartItemId): void
    {
        $cart = $this->cartService->getCart();
        if (!$cart) return;

        $cartItem = $cart->items->firstWhere('id', $cartItemId);
        if (!$cartItem) return;

        // If combo item, show confirmation modal with sibling names
        if ($cartItem->combo_instance_uuid) {
            $siblings = $cart->items
                ->where('combo_instance_uuid', $cartItem->combo_instance_uuid)
                ->where('id', '!=', $cartItemId);

            $siblingNames = $siblings->map(fn ($item) => $item->product->name ?? 'منتج غير معروف')->toArray();

            $this->comboDeleteModal = [
                'show' => true,
                'cartItemId' => $cartItemId,
                'comboUuid' => $cartItem->combo_instance_uuid,
                'siblingNames' => array_values($siblingNames),
            ];
            return;
        }

        // Non-combo: remove directly
        $this->removeItem($cartItemId);
    }

    /**
     * Confirm and execute combo bundle deletion.
     */
    public function confirmComboDelete(): void
    {
        if ($this->comboDeleteModal['cartItemId']) {
            $this->removeItem($this->comboDeleteModal['cartItemId']);
        }
        $this->dismissComboDeleteModal();
    }

    /**
     * Dismiss the combo delete confirmation modal.
     */
    public function dismissComboDeleteModal(): void
    {
        $this->comboDeleteModal = [
            'show' => false,
            'cartItemId' => null,
            'comboUuid' => null,
            'siblingNames' => [],
        ];
    }

    /**
     * Remove item from cart (handles cascade via CartService)
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
        $this->comboSavings = $this->cartService->getComboSavings();
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

        // Group cart items by combo_instance_uuid for visual bundling
        $groupedItems = null;
        if ($cart && $cart->items->count() > 0) {
            $groupedItems = $cart->items->groupBy(function ($item) {
                return $item->combo_instance_uuid ?? 'regular_' . $item->id;
            });
        }

        return view('livewire.store.cart-page', [
            'cart' => $cart,
            'groupedItems' => $groupedItems,
        ])->layout('layouts.store');
    }
}
