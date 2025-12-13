<?php

namespace App\Livewire\Store\Account;

use App\Enums\OrderStatus;
use App\Models\Customer;
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

    /**
     * Get the currently authenticated customer
     */
    private function getCustomerId(): ?int
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->id();
        }
        return null;
    }

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
        $customerId = $this->getCustomerId();

        if (!$customerId) {
            return redirect()->route('login');
        }

        $query = Order::where('customer_id', $customerId)
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
            'all' => Order::where('customer_id', $customerId)->count(),
            'pending' => Order::where('customer_id', $customerId)->where('status', OrderStatus::PENDING)->count(),
            'processing' => Order::where('customer_id', $customerId)->where('status', OrderStatus::PROCESSING)->count(),
            'shipped' => Order::where('customer_id', $customerId)->where('status', OrderStatus::SHIPPED)->count(),
            'delivered' => Order::where('customer_id', $customerId)->where('status', OrderStatus::DELIVERED)->count(),
            'cancelled' => Order::where('customer_id', $customerId)->where('status', OrderStatus::CANCELLED)->count(),
        ];

        return view('livewire.store.account.orders', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
        ])->layout('layouts.store', ['title' => __('messages.account.orders')]);
    }
}
