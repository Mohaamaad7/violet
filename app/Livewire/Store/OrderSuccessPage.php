<?php

namespace App\Livewire\Store;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Confirmed - Violet Store')]
#[Layout('layouts.store')]
class OrderSuccessPage extends Component
{
    public Order $order;

    /**
     * Mount the component with order verification
     * 
     * Security: Ensure user can only view their own orders
     * - Authenticated customers: order must belong to them
     * - Guests: order must be recently created (< 1 hour)
     */
    public function mount(Order $order)
    {
        // Security check - prevent viewing other users' orders
        if (auth('customer')->check()) {
            // Authenticated customer - must own the order
            $customerId = auth('customer')->id();
            if ($order->customer_id != $customerId) {
                abort(403, 'You are not authorized to view this order.');
            }
        } else {
            // Guest - verify by checking if order was created recently
            // This prevents URL sharing abuse while allowing legitimate guest access
            $isRecentOrder = $order->created_at->diffInMinutes(now()) < 60;

            if (!$isRecentOrder) {
                // Redirect to track order page with helpful message
                session()->flash('info', 'This order confirmation link has expired. Please track your order using the form below.');
                return redirect()->route('track-order');
            }
        }

        $this->order = $order->load(['items.product.media', 'shippingAddress']);
    }

    public function render()
    {
        return view('livewire.store.order-success-page');
    }
}
