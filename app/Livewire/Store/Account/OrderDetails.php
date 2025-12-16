<?php

namespace App\Livewire\Store\Account;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetails extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        // Authorization: only owner customer can view
        $customerId = Auth::guard('customer')->check() ? Auth::guard('customer')->id() : null;

        if (!$customerId || $order->customer_id != $customerId) {
            abort(403, __('messages.account.unauthorized'));
        }

        $this->order = $order->load([
            'items.product',
            'shippingAddress',
            'discountCode',
            'statusHistory' => fn($q) => $q->orderBy('created_at', 'desc'),
        ]);
    }

    public function getStatusColorProperty(): string
    {
        return match ($this->order->status) {
            'pending' => 'yellow',
            'processing' => 'blue',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getPaymentStatusColorProperty(): string
    {
        return match ($this->order->payment_status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'failed' => 'red',
            'refunded' => 'purple',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('livewire.store.account.order-details')
            ->layout('layouts.store', ['title' => __('messages.account.order_details') . ' #' . $this->order->order_number]);
    }
}
