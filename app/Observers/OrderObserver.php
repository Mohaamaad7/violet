<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status has changed
        if (!$order->wasChanged('status')) {
            return;
        }

        // CRITICAL: Prevent infinite loop - don't trigger if stock_deducted_at changed
        if ($order->wasChanged('stock_deducted_at') || $order->wasChanged('stock_restored_at')) {
            return;
        }

        $oldStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        // Scenario 1: Order shipped - Deduct stock
        if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
            $this->handleShipment($order);
        }

        // Scenario 2: Order rejected - Restore stock (if it was deducted)
        if ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
            $this->handleRejection($order);
        }
    }

    /**
     * Handle order shipment - Deduct stock automatically.
     */
    protected function handleShipment(Order $order): void
    {
        try {
            $result = $this->orderService->deductStockForOrder($order);

            if ($result['success']) {
                Log::info("Stock deducted automatically for Order #{$order->order_number}");
            } else {
                // Log warning if stock deduction failed
                Log::warning(
                    "Failed to deduct stock for Order #{$order->order_number}: {$result['message']}",
                    ['errors' => $result['errors'] ?? []]
                );
            }
        } catch (\Exception $e) {
            Log::error(
                "Exception during stock deduction for Order #{$order->order_number}: {$e->getMessage()}",
                ['trace' => $e->getTraceAsString()]
            );
        }
    }

    /**
     * Handle order rejection - Restore stock if it was deducted.
     */
    protected function handleRejection(Order $order): void
    {
        // Only restore if stock was deducted
        if (!$order->stock_deducted_at) {
            return;
        }

        try {
            $result = $this->orderService->restockRejectedOrder($order);

            if ($result['success']) {
                Log::info("Stock restored automatically for rejected Order #{$order->order_number}");
            } else {
                Log::warning(
                    "Failed to restore stock for rejected Order #{$order->order_number}: {$result['message']}"
                );
            }
        } catch (\Exception $e) {
            Log::error(
                "Exception during stock restoration for Order #{$order->order_number}: {$e->getMessage()}",
                ['trace' => $e->getTraceAsString()]
            );
        }
    }
}
