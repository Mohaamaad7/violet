<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

/**
 * MergeCartOnLogin - Event Listener
 * 
 * Automatically merges guest cart with customer cart when customer logs in.
 * Handles duplicate products by summing quantities (respecting stock limits).
 * 
 * NOTE: This only applies to Customer model logins, not admin User logins.
 * 
 * @package App\Listeners
 */
class MergeCartOnLogin
{
    /**
     * CartService instance
     */
    protected CartService $cartService;

    /**
     * Create the event listener
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Handle the event
     */
    public function handle(Login $event): void
    {
        // Only merge carts for Customers, not admin Users
        if (!($event->user instanceof Customer)) {
            return;
        }

        // Get guest session ID from cookie
        $guestSessionId = Cookie::get('cart_session_id');

        // If no guest cart exists, nothing to merge
        if (!$guestSessionId) {
            return;
        }

        // Get authenticated customer ID
        $customerId = $event->user->id;

        // Merge guest cart into customer cart
        $this->cartService->mergeGuestCart($guestSessionId, $customerId);
    }
}
