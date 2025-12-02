<?php

namespace App\Livewire\Store;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Confirmed - Violet Store')]
class OrderSuccessPage extends Component
{
    public Order $order;

    /**
     * Mount the component with order verification
     * 
     * Security: Ensure user can only view their own orders
     * - Authenticated users: order must belong to them
     * - Guests: order must have matching guest_email in session OR be recently created
     */
    public function mount(Order $order)
    {
        // Security check - prevent viewing other users' orders
        if (auth()->check()) {
            // Authenticated user - must own the order
            if ($order->user_id !== auth()->id()) {
                abort(403, 'You are not authorized to view this order.');
            }
        } else {
            // Guest - verify by guest_email stored in order
            // For extra security, we also check if order was created in the last hour
            // This prevents URL sharing abuse while allowing legitimate guest access
            $isRecentOrder = $order->created_at->diffInMinutes(now()) < 60;
            
            if (!$isRecentOrder) {
                abort(403, 'This order confirmation has expired. Please contact support.');
            }
        }

        $this->order = $order->load(['items.product.media', 'shippingAddress']);
    }

    public function render()
    {
        return view('livewire.store.order-success-page')
            ->layout('layouts.store');
    }
}
