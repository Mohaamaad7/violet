<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * Record a stock movement
     */
    public function recordMovement(
        int $productId,
        string $type,
        int $quantity,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $batchId = null,
        ?int $variantId = null,
        ?int $warehouseId = null,
        ?string $reasonType = null,
        ?int $responsibleId = null,
        ?float $unitCost = null
    ): StockMovement {
        // Get current stock (from variant or product)
        if ($variantId) {
            $variant = \App\Models\ProductVariant::findOrFail($variantId);
            $stockBefore = $variant->stock;
            $stockAfter = $stockBefore + $quantity;
        } else {
            $product = Product::findOrFail($productId);
            $stockBefore = $product->stock;
            $stockAfter = $stockBefore + $quantity;
        }

        $movement = StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'batch_id' => $batchId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'created_by' => auth()->id(),
            'notes' => $notes,
            'reason_type' => $reasonType,
            'responsible_id' => $responsibleId,
            'unit_cost' => $unitCost,
        ]);

        // Update stock in variant or product (not here - done by caller for stock_count)
        // For other types, update stock directly
        if ($type !== 'stock_count') {
            if ($variantId) {
                $variant->update(['stock' => $stockAfter]);
            } else {
                $product = Product::findOrFail($productId);
                $product->update(['stock' => $stockAfter]);
            }
        }

        // Update batch quantity if batch is specified
        if ($batchId) {
            $batch = Batch::findOrFail($batchId);
            $batch->update([
                'quantity_current' => $batch->quantity_current + $quantity,
            ]);
        }

        return $movement;
    }

    /**
     * Get movement history for a product
     */
    public function getMovementHistory(int $productId, array $filters = []): Collection
    {
        $query = StockMovement::with(['product', 'batch', 'createdBy'])
            ->where('product_id', $productId);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Calculate net stock change for a product in a period
     */
    public function calculateStockChange(int $productId, $startDate, $endDate): array
    {
        $movements = StockMovement::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $summary = [
            'restock' => 0,
            'sale' => 0,
            'return' => 0,
            'adjustment' => 0,
            'expired' => 0,
            'damaged' => 0,
            'net_change' => 0,
        ];

        foreach ($movements as $movement) {
            $summary[$movement->type] += $movement->quantity;
            $summary['net_change'] += $movement->quantity;
        }

        return $summary;
    }

    /**
     * Helper: Deduct stock (for sales)
     */
    public function deductStock(
        int $productId,
        int $quantity,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $batchId = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            'sale',
            -$quantity, // Negative quantity
            $reference,
            $notes,
            $batchId
        );
    }

    /**
     * Helper: Add stock (for returns/restock)
     */
    public function addStock(
        int $productId,
        int $quantity,
        string $type = 'restock',
        ?Model $reference = null,
        ?string $notes = null,
        ?int $batchId = null
    ): StockMovement {
        return $this->recordMovement(
            $productId,
            $type,
            $quantity, // Positive quantity
            $reference,
            $notes,
            $batchId
        );
    }

    /**
     * Get all movements with filters
     */
    public function getAllMovements(array $filters = [], int $perPage = 50)
    {
        $query = StockMovement::with(['product', 'batch', 'createdBy']);

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['type'])) {
            if (is_array($filters['type'])) {
                $query->whereIn('type', $filters['type']);
            } else {
                $query->where('type', $filters['type']);
            }
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStats($startDate = null, $endDate = null): array
    {
        $query = StockMovement::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $movements = $query->get();

        // Group by type for detailed breakdown
        $byType = $movements->groupBy('type')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_quantity' => $items->sum('quantity'),
            ];
        })->toArray();

        return [
            'total_movements' => $movements->count(),
            'by_type' => $byType,
            'restock_count' => $movements->where('type', 'restock')->count(),
            'sale_count' => $movements->where('type', 'sale')->count(),
            'return_count' => $movements->where('type', 'return')->count(),
            'adjustment_count' => $movements->where('type', 'adjustment')->count(),
            'restock_quantity' => $movements->where('type', 'restock')->sum('quantity'),
            'sale_quantity' => abs($movements->where('type', 'sale')->sum('quantity')),
            'return_quantity' => $movements->where('type', 'return')->sum('quantity'),
            'net_change' => $movements->sum('quantity'),
        ];
    }
}
