<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ProductService $productService,
        protected CategoryService $categoryService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view dashboard');
    }

    /**
     * Get dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date']);

            $stats = [
                'orders' => $this->orderService->getOrderStats($filters),
                'products' => [
                    'total' => \App\Models\Product::count(),
                    'active' => \App\Models\Product::active()->count(),
                    'in_stock' => \App\Models\Product::inStock()->count(),
                    'low_stock' => $this->productService->getLowStockProducts()->count(),
                    'out_of_stock' => $this->productService->getOutOfStockProducts()->count(),
                ],
                'categories' => [
                    'total' => \App\Models\Category::count(),
                    'active' => \App\Models\Category::active()->count(),
                ],
                'users' => [
                    'total' => User::count(),
                    'customers' => User::customers()->count(),
                    'influencers' => User::influencers()->count(),
                ],
                'recent_orders' => $this->orderService->getRecentOrders(5),
                'low_stock_products' => $this->productService->getLowStockProducts(5),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
