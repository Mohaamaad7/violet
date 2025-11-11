<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = [
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'in_stock' => Product::where('stock', '>', 0)->count(),
                'low_stock' => Product::whereBetween('stock', [1, 10])->count(),
            ],
            'categories' => [
                'total' => Category::count(),
                'active' => Category::where('is_active', true)->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            ],
            'users' => [
                'total' => User::count(),
                'customers' => User::where('type', 'customer')->count(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.admin', ['title' => 'لوحة التحكم']);
    }
}
