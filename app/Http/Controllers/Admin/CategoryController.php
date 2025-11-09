<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:view categories')->only(['index', 'show']);
        $this->middleware('permission:create categories')->only(['create', 'store']);
        $this->middleware('permission:edit categories')->only(['edit', 'update']);
        $this->middleware('permission:delete categories')->only(['destroy']);
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['active', 'parent_id', 'search']);
            $categories = $this->categoryService->getAllCategories($filters);

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get category tree (hierarchical structure).
     */
    public function tree(): JsonResponse
    {
        try {
            $tree = $this->categoryService->getCategoryTree();

            return response()->json([
                'success' => true,
                'data' => $tree,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category tree',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->findCategory($id);

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->toggleActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update category order.
     */
    public function updateOrder(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['order' => 'required|integer|min:0']);
            
            $category = $this->categoryService->updateOrder($id, $request->order);

            return response()->json([
                'success' => true,
                'message' => 'Category order updated successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move category to different parent.
     */
    public function move(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['parent_id' => 'nullable|exists:categories,id']);
            
            $category = $this->categoryService->moveCategory($id, $request->parent_id);

            return response()->json([
                'success' => true,
                'message' => 'Category moved successfully',
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get category statistics.
     */
    public function stats(int $id): JsonResponse
    {
        try {
            $stats = $this->categoryService->getCategoryStats($id);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
