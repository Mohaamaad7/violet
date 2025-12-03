<?php

namespace App\Livewire\Store;

use App\Models\Order;
use Livewire\Component;

/**
 * Guest Order Tracking Component
 * 
 * Allows guests to track their orders using order number and email/phone.
 * Displays order status timeline and details.
 */
class TrackOrder extends Component
{
    public string $orderNumber = '';
    public string $contactInfo = ''; // email or phone
    public ?Order $order = null;
    public string $errorMessage = '';
    public bool $searched = false;

    protected function rules(): array
    {
        return [
            'orderNumber' => ['required', 'string', 'max:50'],
            'contactInfo' => ['required', 'string', 'max:255'],
        ];
    }

    public function track(): void
    {
        $this->validate();
        $this->searched = true;
        $this->errorMessage = '';
        $this->order = null;

        // Clean up inputs
        $orderNumber = trim($this->orderNumber);
        $contactInfo = trim($this->contactInfo);

        // Find order by order_number and guest_email OR guest_phone
        $order = Order::where('order_number', $orderNumber)
            ->where(function ($query) use ($contactInfo) {
                $query->where('guest_email', $contactInfo)
                      ->orWhere('guest_phone', $contactInfo);
            })
            ->whereNull('user_id') // Guest orders only
            ->first();

        // If not found as guest, check for registered user orders
        if (!$order) {
            $order = Order::where('order_number', $orderNumber)
                ->whereHas('user', function ($query) use ($contactInfo) {
                    $query->where('email', $contactInfo)
                          ->orWhere('phone', $contactInfo);
                })
                ->first();
        }

        if ($order) {
            $this->order = $order->load(['items.product', 'statusHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);
        } else {
            $this->errorMessage = __('messages.track_order.not_found');
        }
    }

    public function clear(): void
    {
        $this->reset(['orderNumber', 'contactInfo', 'order', 'errorMessage', 'searched']);
    }

    /**
     * Get status timeline for the order
     */
    public function getStatusTimelineProperty(): array
    {
        if (!$this->order) {
            return [];
        }

        $statuses = [
            'pending' => [
                'label' => __('messages.track_order.status.pending'),
                'icon' => 'clock',
                'color' => 'gray',
            ],
            'confirmed' => [
                'label' => __('messages.track_order.status.confirmed'),
                'icon' => 'check-circle',
                'color' => 'blue',
            ],
            'processing' => [
                'label' => __('messages.track_order.status.processing'),
                'icon' => 'cog',
                'color' => 'yellow',
            ],
            'shipped' => [
                'label' => __('messages.track_order.status.shipped'),
                'icon' => 'truck',
                'color' => 'purple',
            ],
            'delivered' => [
                'label' => __('messages.track_order.status.delivered'),
                'icon' => 'check-badge',
                'color' => 'green',
            ],
            'cancelled' => [
                'label' => __('messages.track_order.status.cancelled'),
                'icon' => 'x-circle',
                'color' => 'red',
            ],
        ];

        $currentStatus = $this->order->status;
        $timeline = [];
        $statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

        // Handle cancelled status separately
        if ($currentStatus === 'cancelled') {
            // Show all passed statuses then cancelled
            foreach ($statusOrder as $status) {
                $history = $this->order->statusHistory->where('status', $status)->first();
                if ($history) {
                    $timeline[] = [
                        ...$statuses[$status],
                        'status' => $status,
                        'completed' => true,
                        'date' => $history->created_at,
                    ];
                }
            }
            $cancelHistory = $this->order->statusHistory->where('status', 'cancelled')->first();
            $timeline[] = [
                ...$statuses['cancelled'],
                'status' => 'cancelled',
                'completed' => true,
                'current' => true,
                'date' => $cancelHistory?->created_at ?? $this->order->cancelled_at,
            ];
            return $timeline;
        }

        // Normal flow
        $currentIndex = array_search($currentStatus, $statusOrder);
        
        foreach ($statusOrder as $index => $status) {
            $history = $this->order->statusHistory->where('status', $status)->first();
            $isCompleted = $index <= $currentIndex;
            $isCurrent = $status === $currentStatus;
            
            $timeline[] = [
                ...$statuses[$status],
                'status' => $status,
                'completed' => $isCompleted,
                'current' => $isCurrent,
                'date' => $history?->created_at ?? ($isCompleted ? $this->getStatusDate($status) : null),
            ];
        }

        return $timeline;
    }

    /**
     * Get the date for a specific status from order timestamps
     */
    protected function getStatusDate(string $status): ?\DateTime
    {
        return match ($status) {
            'pending' => $this->order->created_at,
            'confirmed' => $this->order->created_at,
            'processing' => $this->order->created_at,
            'shipped' => $this->order->shipped_at,
            'delivered' => $this->order->delivered_at,
            'cancelled' => $this->order->cancelled_at,
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.store.track-order')
            ->layout('layouts.store', [
                'title' => __('messages.track_order.title'),
            ]);
    }
}
