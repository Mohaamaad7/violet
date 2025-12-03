<?php

namespace App\Livewire\Store\Account;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;
    
    public string $status = '';
    public string $search = '';
    
    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
    ];
    
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    
    public function updatingStatus(): void
    {
        $this->resetPage();
    }
    
    public function clearFilters(): void
    {
        $this->reset(['status', 'search']);
        $this->resetPage();
    }
    
    public function render()
    {
        $query = Order::where('user_id', Auth::id())
            ->with(['items.product', 'shippingAddress']);
        
        // Filter by status
        if ($this->status) {
            $query->where('status', $this->status);
        }
        
        // Search by order number
        if ($this->search) {
            $query->where('order_number', 'like', "%{$this->search}%");
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get status counts for filter badges
        $statusCounts = [
            'all' => Order::where('user_id', Auth::id())->count(),
            'pending' => Order::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', Auth::id())->where('status', 'processing')->count(),
            'shipped' => Order::where('user_id', Auth::id())->where('status', 'shipped')->count(),
            'delivered' => Order::where('user_id', Auth::id())->where('status', 'delivered')->count(),
            'cancelled' => Order::where('user_id', Auth::id())->where('status', 'cancelled')->count(),
        ];
        
        return view('livewire.store.account.orders', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
        ])->layout('layouts.store', ['title' => __('messages.account.orders')]);
    }
}
