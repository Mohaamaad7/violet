<?php

namespace App\Livewire\Store\Account;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Get recent orders (last 5)
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Get order statistics
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing_orders' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'delivered_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'processing', 'shipped'])
                ->sum('total'),
        ];
        
        // Get saved addresses count
        $addressesCount = $user->shippingAddresses()->count();
        
        // Get wishlist count
        $wishlistCount = $user->wishlists()->count();
        
        return view('livewire.store.account.dashboard', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'stats' => $stats,
            'addressesCount' => $addressesCount,
            'wishlistCount' => $wishlistCount,
        ])->layout('layouts.store', ['title' => __('messages.account.dashboard')]);
    }
}
