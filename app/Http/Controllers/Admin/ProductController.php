<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view products')->only(['index', 'show']);
        $this->middleware('permission:create products')->only(['create', 'store']);
        $this->middleware('permission:edit products')->only(['edit', 'update']);
        $this->middleware('permission:delete products')->only(['destroy']);
    }

    /**
     * Display a listing of products with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category_id', 'is_active', 'is_featured', 
                'stock_status', 'min_price', 'max_price', 
                'search', 'sort_by', 'sort_order'
            ]);
            
            $perPage = $request->input('per_page', 15);
            $products = $this->productService->getAllProducts($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $products = $this->productService->getFeaturedProducts($limit);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get products on sale.
     */
    public function onSale(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $products = $this->productService->getOnSaleProducts($limit);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products on sale',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->findProduct($id);

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->deleteProduct($id);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $product = $this->productService->toggleActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle product featured status.
     */
    public function toggleFeatured(int $id): JsonResponse
    {
        try {
            $product = $this->productService->toggleFeatured($id);

            return response()->json([
                'success' => true,
                'message' => 'Product featured status updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product featured status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product stock.
     */
    public function updateStock(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['quantity' => 'required|integer|min:0']);
            
            $product = $this->productService->updateStock($id, $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Product stock updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product price.
     */
    public function updatePrice(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0|lt:price',
            ]);
            
            $product = $this->productService->updatePrice(
                $id, 
                $request->price, 
                $request->sale_price
            );

            return response()->json([
                'success' => true,
                'message' => 'Product price updated successfully',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product price',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product statistics.
     */
    public function stats(int $id): JsonResponse
    {
        try {
            $stats = $this->productService->getProductStats($id);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get low stock products.
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $threshold = $request->input('threshold', 10);
            $products = $this->productService->getLowStockProducts($threshold);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch low stock products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get out of stock products.
     */
    public function outOfStock(): JsonResponse
    {
        try {
            $products = $this->productService->getOutOfStockProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch out of stock products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
