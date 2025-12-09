<?php

namespace App\Livewire\Store\Account;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Get the currently authenticated customer
     */
    private function getCustomer(): ?Customer
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }
        return null;
    }

    public function render()
    {
        $customer = $this->getCustomer();

        if (!$customer) {
            return redirect()->route('login');
        }

        $customerId = $customer->id;

        // Get recent orders (last 5)
        $recentOrders = Order::where('customer_id', $customerId)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get order statistics
        $stats = [
            'total_orders' => Order::where('customer_id', $customerId)->count(),
            'pending_orders' => Order::where('customer_id', $customerId)->where('status', 'pending')->count(),
            'processing_orders' => Order::where('customer_id', $customerId)->where('status', 'processing')->count(),
            'delivered_orders' => Order::where('customer_id', $customerId)->where('status', 'delivered')->count(),
            'total_spent' => Order::where('customer_id', $customerId)
                ->whereIn('status', ['delivered', 'processing', 'shipped'])
                ->sum('total'),
        ];

        // Get saved addresses count
        $addressesCount = ShippingAddress::where('customer_id', $customerId)->count();

        // Get wishlist count
        $wishlistCount = Wishlist::where('customer_id', $customerId)->count();

        return view('livewire.store.account.dashboard', [
            'user' => $customer,
            'recentOrders' => $recentOrders,
            'stats' => $stats,
            'addressesCount' => $addressesCount,
            'wishlistCount' => $wishlistCount,
        ])->layout('layouts.store', ['title' => __('messages.account.dashboard')]);
    }
}
