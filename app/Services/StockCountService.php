<?php

namespace App\Services;

use App\Enums\StockCountScope;
use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StockCountService
{
    public function __construct(
        protected StockMovementService $stockMovementService
    ) {
    }

    // ==========================================
    // Create Stock Count
    // ==========================================

    /**
     * Create a new stock count session
     */
    public function createCount(
        int $warehouseId,
        StockCountType $type,
        StockCountScope $scope = StockCountScope::ALL,
        ?array $scopeIds = null,
        ?string $notes = null
    ): StockCount {
        return DB::transaction(function () use ($warehouseId, $type, $scope, $scopeIds, $notes) {
            // Create the stock count
            $stockCount = StockCount::create([
                'code' => StockCount::generateCode(),
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'scope' => $scope,
                'scope_ids' => $scopeIds,
                'status' => StockCountStatus::DRAFT,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Generate items based on scope
            $this->generateCountItems($stockCount);

            return $stockCount->fresh(['items', 'warehouse', 'createdBy']);
        });
    }

    /**
     * Generate stock count items based on scope
     */
    public function generateCountItems(StockCount $stockCount): int
    {
        $products = $this->getProductsForScope($stockCount);
        $itemsCreated = 0;

        foreach ($products as $product) {
            // Check if product has variants
            if ($product->variants->count() > 0) {
                // Create item for each variant
                foreach ($product->variants as $variant) {
                    StockCountItem::create([
                        'stock_count_id' => $stockCount->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'system_quantity' => $variant->stock ?? 0,
                    ]);
                    $itemsCreated++;
                }
            } else {
                // Create item for product only
                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'system_quantity' => $product->stock ?? 0,
                ]);
                $itemsCreated++;
            }
        }

        return $itemsCreated;
    }

    /**
     * Get products based on stock count scope
     */
    protected function getProductsForScope(StockCount $stockCount): Collection
    {
        $query = Product::with('variants')->active();

        switch ($stockCount->scope) {
            case StockCountScope::CATEGORY:
                if (!empty($stockCount->scope_ids)) {
                    // Include subcategories
                    $categoryIds = $this->getCategoryWithChildren($stockCount->scope_ids);
                    $query->whereIn('category_id', $categoryIds);
                }
                break;

            case StockCountScope::PRODUCTS:
                if (!empty($stockCount->scope_ids)) {
                    $query->whereIn('id', $stockCount->scope_ids);
                }
                break;

            case StockCountScope::ALL:
            default:
                // All active products
                break;
        }

        return $query->orderBy('category_id')->orderBy('name')->get();
    }

    /**
     * Get category IDs including all children
     */
    protected function getCategoryWithChildren(array $categoryIds): array
    {
        $allIds = $categoryIds;

        $children = Category::whereIn('parent_id', $categoryIds)->pluck('id')->toArray();

        while (!empty($children)) {
            $allIds = array_merge($allIds, $children);
            $children = Category::whereIn('parent_id', $children)->pluck('id')->toArray();
        }

        return array_unique($allIds);
    }

    // ==========================================
    // Update Items
    // ==========================================

    /**
     * Update a single stock count item
     */
    public function updateCountItem(
        int $itemId,
        ?int $countedQuantity,
        ?string $notes = null
    ): StockCountItem {
        $item = StockCountItem::findOrFail($itemId);

        // Verify the stock count can be edited
        if (!$item->stockCount->canEditItems()) {
            throw new \Exception(__('messages.stock_count.errors.cannot_edit'));
        }

        // If counted_quantity is null (cleared), reset difference
        if ($countedQuantity === null) {
            $item->update([
                'counted_quantity' => null,
                'notes' => $notes,
                'difference' => null,
                'difference_value' => null,
                'counted_by' => null,
                'counted_at' => null,
            ]);
            return $item->fresh();
        }

        $item->update([
            'counted_quantity' => $countedQuantity,
            'notes' => $notes,
            'counted_by' => auth()->id(),
            'counted_at' => now(),
        ]);

        // Recalculate difference
        $difference = $countedQuantity - $item->system_quantity;
        $costPrice = $item->product?->cost_price ?? 0;

        $item->update([
            'difference' => $difference,
            'difference_value' => $difference * $costPrice,
        ]);

        return $item->fresh();
    }

    /**
     * Bulk update stock count items
     */
    public function bulkUpdateItems(int $stockCountId, array $items): int
    {
        $stockCount = StockCount::findOrFail($stockCountId);

        if (!$stockCount->canEdit()) {
            throw new \Exception(__('messages.stock_count.errors.cannot_edit'));
        }

        $updated = 0;

        DB::transaction(function () use ($items, &$updated) {
            foreach ($items as $itemData) {
                if (isset($itemData['id']) && isset($itemData['counted_quantity'])) {
                    $this->updateCountItem(
                        $itemData['id'],
                        $itemData['counted_quantity'],
                        $itemData['notes'] ?? null
                    );
                    $updated++;
                }
            }
        });

        return $updated;
    }

    // ==========================================
    // Status Management
    // ==========================================

    /**
     * Start the stock count
     */
    public function startCount(int $stockCountId): StockCount
    {
        $stockCount = StockCount::findOrFail($stockCountId);

        if ($stockCount->status !== StockCountStatus::DRAFT) {
            throw new \Exception(__('messages.stock_count.errors.invalid_status_transition'));
        }

        $stockCount->update([
            'status' => StockCountStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        return $stockCount->fresh();
    }

    /**
     * Complete the stock count (ready for approval)
     */
    public function completeCount(int $stockCountId): StockCount
    {
        $stockCount = StockCount::findOrFail($stockCountId);

        if (!in_array($stockCount->status, [StockCountStatus::DRAFT, StockCountStatus::IN_PROGRESS])) {
            throw new \Exception(__('messages.stock_count.errors.invalid_status_transition'));
        }

        // Check if all items are counted
        $uncountedItems = $stockCount->items()->uncounted()->count();
        if ($uncountedItems > 0) {
            throw new \Exception(__('messages.stock_count.errors.uncounted_items', ['count' => $uncountedItems]));
        }

        $stockCount->update([
            'status' => StockCountStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        return $stockCount->fresh();
    }

    /**
     * Approve stock count and apply adjustments
     * 
     * @param int $stockCountId
     * @param array $varianceReasons Array of [item_id => ['reason_type' => string, 'responsible_id' => int|null]]
     */
    public function approveCount(int $stockCountId, array $varianceReasons = []): StockCount
    {
        $stockCount = StockCount::findOrFail($stockCountId);

        if ($stockCount->status !== StockCountStatus::COMPLETED) {
            throw new \Exception(__('messages.stock_count.errors.must_complete_first'));
        }

        return DB::transaction(function () use ($stockCount, $varianceReasons) {
            // Apply stock adjustments for items with differences
            $itemsWithDifference = $stockCount->items()
                ->whereNotNull('difference')
                ->where('difference', '!=', 0)
                ->get();

            foreach ($itemsWithDifference as $item) {
                $itemReasons = $varianceReasons[$item->id] ?? [];
                $this->applyStockAdjustment($item, $stockCount, $itemReasons);
            }

            // Update status
            $stockCount->update([
                'status' => StockCountStatus::APPROVED,
                'approved_by' => auth()->id(),
            ]);

            return $stockCount->fresh();
        });
    }

    /**
     * Apply stock adjustment for a single item
     * 
     * @param StockCountItem $item
     * @param StockCount $stockCount
     * @param array $reasons ['reason_type' => string, 'responsible_id' => int|null]
     */
    protected function applyStockAdjustment(StockCountItem $item, StockCount $stockCount, array $reasons = []): void
    {
        $productId = $item->product_id;
        $variantId = $item->variant_id;
        $difference = $item->difference;

        // Get unit cost at time of count (snapshot)
        $unitCost = $item->product?->cost_price ?? 0;

        // Record stock movement with accountability info
        $this->stockMovementService->recordMovement(
            productId: $productId,
            type: 'stock_count',
            quantity: $difference,
            reference: $stockCount,
            notes: __('messages.stock_count.adjustment_note', [
                'code' => $stockCount->code,
                'system' => $item->system_quantity,
                'counted' => $item->counted_quantity,
            ]),
            batchId: null,
            variantId: $variantId,
            warehouseId: $stockCount->warehouse_id,
            reasonType: $reasons['reason_type'] ?? null,
            responsibleId: $reasons['responsible_id'] ?? null,
            unitCost: $unitCost
        );

        // Update stock in product or variant
        if ($variantId) {
            ProductVariant::where('id', $variantId)
                ->increment('stock', $difference);
        } else {
            Product::where('id', $productId)
                ->increment('stock', $difference);
        }
    }

    /**
     * Cancel the stock count
     */
    public function cancelCount(int $stockCountId, ?string $reason = null): StockCount
    {
        $stockCount = StockCount::findOrFail($stockCountId);

        if (!$stockCount->canCancel()) {
            throw new \Exception(__('messages.stock_count.errors.cannot_cancel'));
        }

        $stockCount->update([
            'status' => StockCountStatus::CANCELLED,
            'notes' => $stockCount->notes
                ? $stockCount->notes . "\n\n" . __('messages.stock_count.cancelled_note') . ": " . $reason
                : __('messages.stock_count.cancelled_note') . ": " . $reason,
        ]);

        return $stockCount->fresh();
    }

    // ==========================================
    // Reports & Statistics
    // ==========================================

    /**
     * Get stock count report data
     */
    public function getCountReport(int $stockCountId): array
    {
        $stockCount = StockCount::with([
            'warehouse',
            'createdBy',
            'approvedBy',
            'items' => function ($query) {
                $query->with(['product', 'variant', 'countedBy'])
                    ->orderBy('product_id');
            }
        ])->findOrFail($stockCountId);

        $items = $stockCount->items;

        return [
            'stock_count' => $stockCount,
            'summary' => [
                'total_items' => $items->count(),
                'counted_items' => $items->whereNotNull('counted_quantity')->count(),
                'matched_items' => $items->where('difference', 0)->count(),
                'shortage_items' => $items->where('difference', '<', 0)->count(),
                'surplus_items' => $items->where('difference', '>', 0)->count(),
                'total_shortage_qty' => abs($items->where('difference', '<', 0)->sum('difference')),
                'total_surplus_qty' => $items->where('difference', '>', 0)->sum('difference'),
                'total_shortage_value' => abs($items->where('difference_value', '<', 0)->sum('difference_value')),
                'total_surplus_value' => $items->where('difference_value', '>', 0)->sum('difference_value'),
                'net_difference_qty' => $items->sum('difference'),
                'net_difference_value' => $items->sum('difference_value'),
            ],
            'shortage_items' => $items->where('difference', '<', 0)->values(),
            'surplus_items' => $items->where('difference', '>', 0)->values(),
            'matched_items' => $items->where('difference', 0)->values(),
        ];
    }

    /**
     * Get all items for count sheet (for PDF/Excel export)
     */
    public function getCountSheetData(int $stockCountId): array
    {
        $stockCount = StockCount::with([
            'warehouse',
            'items' => function ($query) {
                $query->with(['product.category', 'variant'])
                    ->orderBy('product_id');
            }
        ])->findOrFail($stockCountId);

        return [
            'stock_count' => $stockCount,
            'items' => $stockCount->items->map(function ($item) {
                return [
                    'sku' => $item->sku,
                    'product_name' => $item->product?->name,
                    'variant_name' => $item->variant?->name,
                    'category' => $item->product?->category?->name,
                    'system_quantity' => $item->system_quantity,
                    'counted_quantity' => $item->counted_quantity,
                    'difference' => $item->difference,
                ];
            }),
        ];
    }

    /**
     * Get pending stock counts for dashboard widget
     */
    public function getPendingCounts(): Collection
    {
        return StockCount::with(['warehouse', 'createdBy'])
            ->pending()
            ->latest()
            ->limit(5)
            ->get();
    }
}
