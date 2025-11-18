<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;

/**
 * MergeCartOnLogin - Event Listener
 * 
 * Automatically merges guest cart with user cart when user logs in.
 * Handles duplicate products by summing quantities (respecting stock limits).
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
        // Get guest session ID from cookie
        $guestSessionId = Cookie::get('cart_session_id');

        // If no guest cart exists, nothing to merge
        if (!$guestSessionId) {
            return;
        }

        // Get authenticated user ID
        $userId = $event->user->id;

        // Merge guest cart into user cart
        $this->cartService->mergeGuestCart($guestSessionId, $userId);
    }
}
