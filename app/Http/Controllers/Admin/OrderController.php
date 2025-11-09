<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view orders')->only(['index', 'show']);
        $this->middleware('permission:edit orders')->only(['update', 'updateStatus', 'cancelOrder']);
    }

    /**
     * Display a listing of orders with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'payment_status', 'payment_method',
                'user_id', 'start_date', 'end_date', 
                'search', 'sort_by', 'sort_order'
            ]);
            
            $perPage = $request->input('per_page', 15);
            $orders = $this->orderService->getAllOrders($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->findOrder($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
                'notes' => 'nullable|string',
            ]);

            $order = $this->orderService->updateStatus(
                $id, 
                $request->status, 
                $request->notes,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:pending,paid,failed,refunded',
                'transaction_id' => 'nullable|string',
            ]);

            $order = $this->orderService->updatePaymentStatus(
                $id, 
                $request->payment_status,
                $request->transaction_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $order = $this->orderService->cancelOrder(
                $id,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date']);
            $stats = $this->orderService->getOrderStats($filters);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent orders.
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $orders = $this->orderService->getRecentOrders($limit);

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
