<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\DiscountCode;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ShippingAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected ProductService $productService,
        protected EmailService $emailService
    ) {
    }

    /**
     * Get all orders with filters and pagination
     */
    public function getAllOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment status
        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Filter by payment method
        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Search by order number
        if (isset($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find order by ID
     */
    public function findOrder(int $id): ?Order
    {
        return Order::with([
            'user',
            'items.product',
            'shippingAddress',
            'discountCode',
            'statusHistory.user',
        ])->findOrFail($id);
    }

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with([
                'user',
                'items.product',
                'shippingAddress',
                'discountCode',
                'statusHistory.user',
            ])
            ->firstOrFail();
    }

    /**
     * Create new order
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Generate temporary order number
            $tempOrderNumber = 'TEMP-' . uniqid();

            // Create order
            $order = Order::create([
                'order_number' => $tempOrderNumber,
                'user_id' => $data['user_id'],
                'status' => OrderStatus::PENDING,
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'],
                'subtotal' => $data['subtotal'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total' => $data['total'],
                'notes' => $data['notes'] ?? null,
                'discount_code_id' => $data['discount_code_id'] ?? null,
                'shipping_address_id' => $data['shipping_address_id'],
            ]);

            // Create order items and decrease stock
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                // Decrease product stock
                $this->productService->decreaseStock($item['product_id'], $item['quantity']);
            }

            // Create initial status history
            $this->addStatusHistory($order->id, 'pending', 'Order created', auth()->id());

            // Update order number with actual ID
            $date = date('Ymd');  // 20251216
            $time = date('His');  // 142500
            $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);  // 000123
            $finalOrderNumber = "VLT-{$date}-{$time}-{$orderId}";

            $order->update(['order_number' => $finalOrderNumber]);

            return $order->fresh();
        });
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status, ?string $notes = null, ?int $changedBy = null): Order
    {
        $order = $this->findOrder($id);

        $previousStatus = $order->status;

        // Convert string to enum
        $statusEnum = OrderStatus::fromString($status);

        // Update status
        $order->update(['status' => $statusEnum]);

        // Update status-specific timestamps
        match ($statusEnum) {
            OrderStatus::PROCESSING => $order->update(['shipped_at' => null]),
            OrderStatus::SHIPPED => $order->update(['shipped_at' => now()]),
            OrderStatus::DELIVERED => $order->update(['delivered_at' => now()]),
            OrderStatus::CANCELLED => $this->handleCancellation($order, $notes),
            default => null
        };

        // DIRECT CALL: Handle stock deduction when shipped
        if ($statusEnum === OrderStatus::SHIPPED && $previousStatus !== OrderStatus::SHIPPED) {
            $stockResult = $this->deductStockForOrder($order);
            if (!$stockResult['success']) {
                \Log::warning("Stock deduction failed for Order #{$order->order_number}: {$stockResult['message']}");
            }
        }

        // DIRECT CALL: Handle stock restoration when cancelled (if was shipped)
        if ($statusEnum === OrderStatus::CANCELLED && $previousStatus === OrderStatus::SHIPPED && $order->stock_deducted_at) {
            $restockResult = $this->restockRejectedOrder($order);
            if (!$restockResult['success']) {
                \Log::warning("Stock restoration failed for Order #{$order->order_number}: {$restockResult['message']}");
            }
        }

        // Reload relationships for the updated order
        $order->load(['items.product', 'user', 'customer']);

        // Add to status history (pass string for history table)
        $this->addStatusHistory($id, $statusEnum->toString(), $notes, $changedBy ?? auth()->id());

        // Send status update email to customer in background (non-blocking)
        if ($previousStatus !== $statusEnum) {
            try {
                // Dispatch email job to queue instead of sending immediately
                dispatch(function () use ($order) {
                    app(\App\Services\EmailService::class)->sendOrderStatusUpdate($order);
                })->afterResponse();
            } catch (\Exception $e) {
                // Log error but don't fail the status update
                report($e);
            }
        }

        return $order;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $id, string $paymentStatus, ?string $transactionId = null): Order
    {
        $order = $this->findOrder($id);
        $previousPaymentStatus = $order->payment_status;

        $updateData = ['payment_status' => $paymentStatus];

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = now();
            if ($transactionId) {
                $updateData['payment_transaction_id'] = $transactionId;
            }
        }

        $order->update($updateData);

        // Record influencer commission when payment is confirmed
        if ($paymentStatus === 'paid' && $previousPaymentStatus !== 'paid') {
            $this->recordInfluencerCommission($order->fresh());
        }

        return $order->fresh();
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $id, string $reason, ?int $cancelledBy = null): Order
    {
        $order = $this->findOrder($id);

        if (in_array($order->status, [OrderStatus::DELIVERED, OrderStatus::CANCELLED])) {
            throw new \Exception("Cannot cancel order with status: {$order->status->toString()}");
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Restore product stock
        foreach ($order->items as $item) {
            $this->productService->increaseStock($item->product_id, $item->quantity);
        }

        // Add to status history
        $this->addStatusHistory($id, 'cancelled', "Reason: {$reason}", $cancelledBy ?? auth()->id());

        return $order->fresh();
    }

    /**
     * Handle order cancellation
     */
    protected function handleCancellation(Order $order, ?string $reason): void
    {
        $order->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Reverse influencer commission if exists
        $this->reverseInfluencerCommission($order);

        // NOTE: Stock restoration is now handled explicitly in updateStatus()
        // using restockRejectedOrder() method, which checks stock_deducted_at
        // and creates proper stock movement records
    }

    /**
     * Record influencer commission when order is paid
     * Only records if the order used an influencer's discount code
     * and the influencer is active
     */
    protected function recordInfluencerCommission(Order $order): void
    {
        // Check if order has a discount code
        if (!$order->discount_code_id) {
            return;
        }

        $discountCode = DiscountCode::find($order->discount_code_id);

        // Check if discount code belongs to an influencer
        if (!$discountCode || !$discountCode->influencer_id) {
            return;
        }

        $influencer = Influencer::find($discountCode->influencer_id);

        // Edge Case: Suspended influencer doesn't get commission
        if (!$influencer || $influencer->status !== 'active') {
            \Log::info("Influencer commission skipped for Order #{$order->order_number}: Influencer is not active");
            return;
        }

        // Edge Case: Disabled discount code doesn't record commission
        if (!$discountCode->is_active) {
            \Log::info("Influencer commission skipped for Order #{$order->order_number}: Discount code is not active");
            return;
        }

        // Check if commission already recorded for this order
        $existingCommission = InfluencerCommission::where('order_id', $order->id)->first();
        if ($existingCommission) {
            \Log::info("Influencer commission already exists for Order #{$order->order_number}");
            return;
        }

        // Calculate commission
        $commissionRate = $discountCode->commission_value ?? $influencer->commission_rate;
        $commissionAmount = $order->total * ($commissionRate / 100);

        // Record commission using InfluencerService
        try {
            app(InfluencerService::class)->recordCommission([
                'influencer_id' => $influencer->id,
                'order_id' => $order->id,
                'discount_code_id' => $discountCode->id,
                'order_amount' => $order->total,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'status' => 'pending',
            ]);

            // Update influencer statistics
            $influencer->increment('total_sales', $order->total);
            $influencer->increment('total_commission_earned', $commissionAmount);
            $influencer->increment('balance', $commissionAmount);

            \Log::info("Influencer commission recorded for Order #{$order->order_number}: {$commissionAmount} EGP");
        } catch (\Exception $e) {
            \Log::error("Failed to record influencer commission for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Reverse influencer commission when order is cancelled or refunded
     */
    protected function reverseInfluencerCommission(Order $order): void
    {
        // Find pending commission for this order
        $commission = InfluencerCommission::where('order_id', $order->id)
            ->where('status', 'pending')
            ->first();

        if (!$commission) {
            return;
        }

        $influencer = $commission->influencer;

        // Update commission status to cancelled
        $commission->update(['status' => 'cancelled']);

        // Reverse influencer statistics
        if ($influencer) {
            $influencer->decrement('total_sales', $commission->order_amount);
            $influencer->decrement('total_commission_earned', $commission->commission_amount);
            $influencer->decrement('balance', $commission->commission_amount);

            \Log::info("Influencer commission reversed for Order #{$order->order_number}: {$commission->commission_amount} EGP");
        }
    }

    /**
     * Add status to history
     */
    protected function addStatusHistory(int $orderId, string $status, ?string $notes = null, ?int $changedBy = null): OrderStatusHistory
    {
        return OrderStatusHistory::create([
            'order_id' => $orderId,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $changedBy,
        ]);
    }

    /**
     * Generate unique order number
     * Format: VLT-DATE-TIME-UNIQUEID
     * Example: VLT-20251216-140530-A3F9B2
     */
    protected function generateOrderNumber(): string
    {
        do {
            $date = date('Ymd');  // 20251216
            $time = date('His');  // 140530
            $uniqueId = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));  // A3F9B2

            $orderNumber = "VLT-{$date}-{$time}-{$uniqueId}";
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Calculate order totals
     */
    public function calculateTotals(array $items, ?string $discountCode = null, float $shippingCost = 0): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $discountAmount = 0;
        if ($discountCode) {
            // TODO: Implement discount code logic
        }

        $taxAmount = $subtotal * 0.14; // 14% VAT in Egypt
        $total = $subtotal - $discountAmount + $shippingCost + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'shipping_cost' => round($shippingCost, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Get order statistics
     */
    public function getOrderStats(array $filters = []): array
    {
        $query = Order::query();

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return [
            'total_orders' => (clone $query)->count(),
            'pending_orders' => (clone $query)->where('status', OrderStatus::PENDING)->count(),
            'processing_orders' => (clone $query)->where('status', OrderStatus::PROCESSING)->count(),
            'shipped_orders' => (clone $query)->where('status', OrderStatus::SHIPPED)->count(),
            'delivered_orders' => (clone $query)->where('status', OrderStatus::DELIVERED)->count(),
            'cancelled_orders' => (clone $query)->where('status', OrderStatus::CANCELLED)->count(),
            'total_revenue' => (clone $query)->where('payment_status', 'paid')->sum('total'),
            'pending_revenue' => (clone $query)->where('payment_status', 'pending')->sum('total'),
        ];
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Deduct stock when order is shipped
     * 
     * @param Order $order
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     * @throws \Exception
     */
    public function deductStockForOrder(Order $order): array
    {
        // Check if stock already deducted
        if ($order->stock_deducted_at) {
            return [
                'success' => false,
                'message' => 'تم خصم المخزون لهذا الطلب مسبقاً',
                'errors' => [],
            ];
        }

        $stockMovementService = app(StockMovementService::class);
        $errors = [];
        $insufficientStock = [];

        // NOTE: No DB::transaction() here because Observer runs inside parent transaction
        try {
            // Step 1: Validate stock availability for all items
            foreach ($order->items as $item) {
                $product = $item->product;

                if (!$product) {
                    $errors[] = "المنتج غير موجود (Item ID: {$item->id})";
                    continue;
                }

                $currentStock = $product->stock;
                $requiredQuantity = $item->quantity;

                if ($currentStock < $requiredQuantity) {
                    $insufficientStock[] = [
                        'product' => $product->name,
                        'sku' => $product->sku,
                        'required' => $requiredQuantity,
                        'available' => $currentStock,
                    ];
                }
            }

            // If insufficient stock, return error
            if (!empty($insufficientStock)) {
                $errorMessage = 'المخزون غير كافي للمنتجات التالية: ';
                $details = [];
                foreach ($insufficientStock as $issue) {
                    $details[] = "{$issue['product']} (SKU: {$issue['sku']}): مطلوب {$issue['required']}, متوفر {$issue['available']}";
                }
                $errorMessage .= implode(' | ', $details);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $insufficientStock,
                ];
            }

            // Step 2: Deduct stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $stockMovementService->deductStock(
                        $item->product->id,
                        $item->quantity,
                        $order,
                        "Order #{$order->order_number}"
                    );
                }
            }

            // Step 3: Mark stock as deducted
            $order->stock_deducted_at = now();
            $order->save();

            return [
                'success' => true,
                'message' => 'تم خصم المخزون بنجاح',
                'errors' => [],
            ];

        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء خصم المخزون: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Restore stock when order is rejected
     * 
     * @param Order $order
     * @return array ['success' => bool, 'message' => string]
     */
    public function restockRejectedOrder(Order $order): array
    {
        // Check if stock was deducted
        if (!$order->stock_deducted_at) {
            return [
                'success' => false,
                'message' => 'لم يتم خصم المخزون لهذا الطلب من الأساس',
            ];
        }

        // Check if already restocked
        if ($order->stock_restored_at) {
            return [
                'success' => false,
                'message' => 'تم إرجاع المخزون لهذا الطلب مسبقاً',
            ];
        }

        $stockMovementService = app(StockMovementService::class);

        // NOTE: No DB::transaction() here because Observer runs inside parent transaction
        try {
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $stockMovementService->addStock(
                        $item->product->id,
                        $item->quantity,
                        'return',
                        $order,
                        "Order #{$order->order_number} (Rejected)"
                    );
                }
            }

            // Mark stock as restored
            $order->stock_restored_at = now();
            $order->save();

            return [
                'success' => true,
                'message' => 'تم إرجاع المخزون بنجاح',
            ];

        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء إرجاع المخزون: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if order can be shipped (stock validation)
     * 
     * @param Order $order
     * @return array ['canShip' => bool, 'issues' => array]
     */
    public function validateStockForShipment(Order $order): array
    {
        $issues = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product) {
                $issues[] = [
                    'product' => $item->product_name ?? 'Unknown',
                    'sku' => $item->product_sku ?? 'N/A',
                    'required' => $item->quantity,
                    'available' => 0,
                    'message' => 'المنتج غير موجود',
                ];
                continue;
            }

            $currentStock = $product->stock;
            $requiredQuantity = $item->quantity;

            if ($currentStock < $requiredQuantity) {
                $issues[] = [
                    'product' => $product->name,
                    'sku' => $product->sku,
                    'required' => $requiredQuantity,
                    'available' => $currentStock,
                    'message' => "المخزون غير كافي (متوفر: {$currentStock}, مطلوب: {$requiredQuantity})",
                ];
            }
        }

        return [
            'canShip' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Mark order as rejected
     */
    public function markAsRejected(int $orderId, string $reason): Order
    {
        return DB::transaction(function () use ($orderId, $reason) {
            $order = $this->findOrder($orderId);

            if (!in_array($order->status, [OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::SHIPPED])) {
                throw new \Exception("Order cannot be rejected in current status");
            }

            // If order was shipped, restock items
            if ($order->status === OrderStatus::SHIPPED && $order->shipped_at) {
                $this->restockRejectedOrder($order);
            }

            $order->update([
                'status' => OrderStatus::CANCELLED,
                'return_status' => 'none',
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->addStatusHistory($orderId, 'cancelled', "Rejected: {$reason}", auth()->id());

            return $order->fresh();
        });
    }
}
