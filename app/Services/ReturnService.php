<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(
        protected StockMovementService $stockMovementService
    ) {
    }

    /**
     * Create a return request
     */
    public function createReturnRequest(int $orderId, array $data): OrderReturn
    {
        return DB::transaction(function () use ($orderId, $data) {
            $order = Order::with('items')->findOrFail($orderId);

            // Validate order can be returned
            $this->validateReturnRequest($order, $data['type']);

            // Auto-approve rejections if enabled
            $autoApprove = $data['type'] === 'rejection' && (bool) setting('auto_approve_rejections', false);

            // Create return
            $return = OrderReturn::create([
                'order_id' => $orderId,
                'return_number' => OrderReturn::generateReturnNumber(),
                'type' => $data['type'],
                'status' => $autoApprove ? 'approved' : 'pending',
                'reason' => $data['reason'],
                'customer_notes' => $data['customer_notes'] ?? null,
                'approved_at' => $autoApprove ? now() : null,
            ]);

            // Create return items
            // If rejection (shipped order), include ALL items automatically
            // If return_after_delivery (delivered order), use selected items from form
            if ($data['type'] === 'rejection') {
                // رفض الاستلام: إضافة جميع أصناف الطلب تلقائياً
                $items = $order->items->map(fn($item) => [
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity
                ])->toArray();
            } else {
                // استرجاع بعد التسليم: استخدام الأصناف المختارة من النموذج
                $items = $data['items'] ?? [];
            }

            foreach ($items as $itemData) {
                // دعم كل من الصيغة القديمة (array of IDs) والصيغة الجديدة (array of objects)
                $orderItemId = is_array($itemData) ? $itemData['order_item_id'] : $itemData;
                $orderItem = $order->items->firstWhere('id', $orderItemId);

                if ($orderItem) {
                    // تحديد الكمية المراد إرجاعها
                    $quantityToReturn = $orderItem->quantity; // القيمة الافتراضية: كل الكمية

                    // إذا كانت البيانات من النموذج الجديد وتحتوي على كمية محددة
                    if (is_array($itemData) && isset($itemData['quantity'])) {
                        $quantityToReturn = (int) $itemData['quantity'];

                        // التحقق من صحة الكمية
                        if ($quantityToReturn < 1 || $quantityToReturn > $orderItem->quantity) {
                            throw new \Exception("Invalid return quantity for {$orderItem->product_name}");
                        }
                    }

                    ReturnItem::create([
                        'return_id' => $return->id,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'product_name' => $orderItem->product_name,
                        'product_sku' => $orderItem->product_sku ?? $orderItem->product->sku,
                        'quantity' => $quantityToReturn,
                        'price' => $orderItem->price,
                    ]);
                }
            }

            // Update order return status
            $order->update(['return_status' => 'requested']);

            return $return->fresh(['items', 'order']);
        });
    }

    /**
     * Approve return
     */
    public function approveReturn(int $returnId, int $adminId, ?string $adminNotes = null): OrderReturn
    {
        return DB::transaction(function () use ($returnId, $adminId, $adminNotes) {
            $return = OrderReturn::with(['order', 'items'])->findOrFail($returnId);

            if ($return->status !== 'pending') {
                throw new \Exception("Return is not in pending status");
            }

            $return->update([
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            $return->order->update(['return_status' => 'approved']);

            return $return->fresh();
        });
    }

    /**
     * Reject return
     */
    public function rejectReturn(int $returnId, int $adminId, string $reason): OrderReturn
    {
        return DB::transaction(function () use ($returnId, $adminId, $reason) {
            $return = OrderReturn::findOrFail($returnId);

            if ($return->status !== 'pending') {
                throw new \Exception("Return is not in pending status");
            }

            $return->update([
                'status' => 'rejected',
                'rejected_by' => $adminId,
                'rejected_at' => now(),
                'admin_notes' => $reason,
            ]);

            $return->order->update(['return_status' => 'none']);

            return $return->fresh();
        });
    }

    /**
     * Process return (restock items)
     */
    public function processReturn(int $returnId, array $itemConditions, int $adminId): OrderReturn
    {
        return DB::transaction(function () use ($returnId, $itemConditions, $adminId) {
            $return = OrderReturn::with(['order', 'items.product'])->findOrFail($returnId);

            if ($return->status !== 'approved') {
                throw new \Exception("Return must be approved first");
            }

            $refundAmount = 0;

            foreach ($return->items as $item) {
                // Try both return_item->id and order_item_id as keys
                $itemData = $itemConditions[$item->id] ?? $itemConditions[$item->order_item_id] ?? [];
                $condition = $itemData['condition'] ?? 'good';
                $receivedQuantity = $itemData['received_quantity'] ?? $item->quantity;
                $shouldRestock = $itemData['restock'] ?? true;

                // Update item condition
                $item->update(['condition' => $condition]);

                // Restock if condition allows and admin decided to restock
                if ($shouldRestock && in_array($condition, ['good', 'opened'])) {
                    $this->restockItem($item, $receivedQuantity);
                    $refundAmount += ($receivedQuantity * $item->price);
                }
            }

            // Update return
            $return->update([
                'status' => 'completed',
                'processed_by' => $adminId,
                'processed_at' => now(),
                'refund_amount' => $refundAmount,
                'refund_status' => $refundAmount > 0 ? 'pending' : 'completed',
            ]);

            $return->order->update(['return_status' => 'completed']);

            return $return->fresh();
        });
    }

    /**
     * Complete return (mark refund as completed)
     */
    public function completeReturn(int $returnId, int $adminId): OrderReturn
    {
        $return = OrderReturn::findOrFail($returnId);

        if ($return->status !== 'completed') {
            throw new \Exception("Return must be processed first");
        }

        $return->update([
            'refund_status' => 'completed',
        ]);

        return $return->fresh();
    }

    /**
     * Helper: Restock a single return item
     */
    protected function restockItem(ReturnItem $item, ?int $quantity = null): void
    {
        $quantityToRestock = $quantity ?? $item->quantity;

        $this->stockMovementService->addStock(
            $item->product_id,
            $quantityToRestock,
            'return',
            $item->return,
            "Returned from order #{$item->return->order->order_number}"
        );

        $item->update([
            'restocked' => true,
            'restocked_at' => now(),
        ]);
    }

    /**
     * Helper: Restock all items in a return
     */
    public function restockReturnedItems(int $returnId): int
    {
        $return = OrderReturn::with('items')->findOrFail($returnId);
        $count = 0;

        foreach ($return->items as $item) {
            if (!$item->restocked && $item->canBeRestocked()) {
                $this->restockItem($item);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Validate return request
     */
    protected function validateReturnRequest(Order $order, string $type): void
    {
        if ($type === 'rejection') {
            if (!in_array($order->status, ['pending', 'processing', 'shipped'])) {
                throw new \Exception("Order cannot be rejected in current status");
            }
        }

        if ($type === 'return_after_delivery') {
            if ($order->status !== 'delivered') {
                throw new \Exception("Order must be delivered first");
            }

            // Check return window (default: 14 days)
            // Prioritize DB setting over config
            $returnWindowDays = (int) (setting('return_window_days') ?? config('app.return_window_days', 14));
            if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > $returnWindowDays) {
                throw new \Exception("Return window has expired (allowed: {$returnWindowDays} days)");
            }
        }

        // Check if already has pending/approved return
        if ($order->returns()->whereIn('status', ['pending', 'approved'])->exists()) {
            throw new \Exception("Order already has a pending or approved return request");
        }
    }

    /**
     * Get all returns with filters
     */
    public function getAllReturns(array $filters = [])
    {
        $query = OrderReturn::with(['order.customer', 'items']);

        if (isset($filters['type'])) {
            if (is_array($filters['type'])) {
                $query->whereIn('type', $filters['type']);
            } else {
                $query->where('type', $filters['type']);
            }
        }

        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $query->where('return_number', 'like', "%{$filters['search']}%")
                ->orWhereHas('order', function ($q) use ($filters) {
                    $q->where('order_number', 'like', "%{$filters['search']}%");
                });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Get return statistics
     */
    public function getReturnStats($startDate = null, $endDate = null): array
    {
        $query = OrderReturn::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $returns = $query->with('items')->get();
        $orders = Order::whereBetween('created_at', [$startDate ?? now()->subMonth(), $endDate ?? now()])->count();

        // Group by status and type
        $byStatus = $returns->groupBy('status')->map(fn($items) => $items->count())->toArray();
        $byType = $returns->groupBy('type')->map(fn($items) => $items->count())->toArray();

        return [
            'total_returns' => $returns->count(),
            'by_status' => $byStatus,
            'by_type' => $byType,
            'rejection_count' => $returns->where('type', 'rejection')->count(),
            'return_after_delivery_count' => $returns->where('type', 'return_after_delivery')->count(),
            'pending_count' => $returns->where('status', 'pending')->count(),
            'approved_count' => $returns->where('status', 'approved')->count(),
            'rejected_count' => $returns->where('status', 'rejected')->count(),
            'completed_count' => $returns->where('status', 'completed')->count(),
            'return_rate' => $orders > 0 ? round(($returns->count() / $orders) * 100, 2) : 0,
            'total_refund_amount' => $returns->sum('refund_amount'),
            'average_refund_amount' => $returns->where('refund_amount', '>', 0)->avg('refund_amount') ?? 0,
        ];
    }

    /**
     * Get returns by reason
     */
    public function getReturnsByReason($startDate = null, $endDate = null): Collection
    {
        $query = OrderReturn::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->selectRaw('reason, COUNT(*) as count')
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->get();
    }
}
