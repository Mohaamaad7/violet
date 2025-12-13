<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BatchService
{
    public function __construct(
        protected StockMovementService $stockMovementService
    ) {}

    /**
     * Create a new batch
     */
    public function createBatch(array $data): Batch
    {
        return DB::transaction(function () use ($data) {
            // Set quantity_current to 0 initially - will be updated by stock movement
            $data['quantity_current'] = 0;
            
            $batch = Batch::create($data);

            // Record stock movement for initial quantity
            if ($batch->quantity_initial > 0) {
                $this->stockMovementService->addStock(
                    $batch->product_id,
                    $batch->quantity_initial,
                    'restock',
                    $batch,
                    "New batch created: {$batch->batch_number}",
                    $batch->id
                );
            }

            return $batch->fresh();
        });
    }

    /**
     * Update batch
     */
    public function updateBatch(int $id, array $data): Batch
    {
        $batch = Batch::findOrFail($id);
        
        // Don't allow changing initial quantity after creation
        unset($data['quantity_initial'], $data['quantity_current']);
        
        $batch->update($data);

        return $batch->fresh();
    }

    /**
     * Deduct from batch
     */
    public function deductFromBatch(
        int $batchId,
        int $quantity,
        $reference = null,
        ?string $notes = null
    ): void {
        $batch = Batch::findOrFail($batchId);

        if ($batch->quantity_current < $quantity) {
            throw new \Exception("Insufficient quantity in batch. Available: {$batch->quantity_current}, Required: {$quantity}");
        }

        $this->stockMovementService->deductStock(
            $batch->product_id,
            $quantity,
            $reference,
            $notes,
            $batch->id
        );
    }

    /**
     * Add to batch
     */
    public function addToBatch(
        int $batchId,
        int $quantity,
        $reference = null,
        ?string $notes = null
    ): void {
        $batch = Batch::findOrFail($batchId);

        $this->stockMovementService->addStock(
            $batch->product_id,
            $quantity,
            'restock',
            $reference,
            $notes,
            $batch->id
        );
    }

    /**
     * Get expiring batches
     */
    public function getExpiringBatches(int $days = 30): Collection
    {
        return Batch::with('product')
            ->expiring($days)
            ->where('quantity_current', '>', 0)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get expired batches
     */
    public function getExpiredBatches(): Collection
    {
        return Batch::with('product')
            ->expired()
            ->where('quantity_current', '>', 0)
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Mark batch as expired
     */
    public function markAsExpired(int $batchId, ?string $notes = null): Batch
    {
        return DB::transaction(function () use ($batchId, $notes) {
            $batch = Batch::findOrFail($batchId);

            // If there's remaining stock, mark it as expired in movements
            if ($batch->quantity_current > 0) {
                $this->stockMovementService->recordMovement(
                    $batch->product_id,
                    'expired',
                    -$batch->quantity_current,
                    $batch,
                    $notes ?? "Batch {$batch->batch_number} expired",
                    $batch->id
                );
            }

            $batch->update(['status' => 'expired']);

            return $batch->fresh();
        });
    }

    /**
     * Mark batch as disposed
     */
    public function markAsDisposed(int $batchId, ?string $notes = null): Batch
    {
        return DB::transaction(function () use ($batchId, $notes) {
            $batch = Batch::findOrFail($batchId);

            // If there's remaining stock, mark it as damaged in movements
            if ($batch->quantity_current > 0) {
                $this->stockMovementService->recordMovement(
                    $batch->product_id,
                    'damaged',
                    -$batch->quantity_current,
                    $batch,
                    $notes ?? "Batch {$batch->batch_number} disposed",
                    $batch->id
                );
            }

            $batch->update(['status' => 'disposed']);

            return $batch->fresh();
        });
    }

    /**
     * Get all batches with filters
     */
    public function getAllBatches(array $filters = [])
    {
        $query = Batch::with('product');

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (isset($filters['expiring_days'])) {
            $query->expiring($filters['expiring_days']);
        }

        if (isset($filters['search'])) {
            $query->where('batch_number', 'like', "%{$filters['search']}%");
        }

        $sortBy = $filters['sort_by'] ?? 'expiry_date';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Get batch details with movement history
     */
    public function getBatchDetails(int $id): array
    {
        $batch = Batch::with(['product', 'stockMovements.createdBy'])->findOrFail($id);

        return [
            'batch' => $batch,
            'movements' => $batch->stockMovements()->latest()->get(),
            'stats' => [
                'initial_quantity' => $batch->quantity_initial,
                'current_quantity' => $batch->quantity_current,
                'total_sold' => $batch->stockMovements()->where('type', 'sale')->sum('quantity'),
                'total_returned' => $batch->stockMovements()->where('type', 'return')->sum('quantity'),
                'days_until_expiry' => $batch->days_until_expiry,
                'alert_level' => $batch->alert_level,
            ],
        ];
    }

    /**
     * Auto-mark expired batches
     */
    public function autoMarkExpiredBatches(): int
    {
        $expiredBatches = Batch::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredBatches as $batch) {
            $this->markAsExpired($batch->id, 'Auto-marked as expired by system');
            $count++;
        }

        return $count;
    }

    /**
     * Get batch statistics
     */
    public function getBatchStats(): array
    {
        $batches = Batch::all();
        $byStatus = $batches->groupBy('status')->map(fn($items) => $items->count())->toArray();
        
        return [
            'total_batches' => Batch::count(),
            'by_status' => $byStatus,
            'active_batches' => Batch::where('status', 'active')->count(),
            'expired_batches' => Batch::where('status', 'expired')->count(),
            'disposed_batches' => Batch::where('status', 'disposed')->count(),
            'critical_batches' => Batch::expiring(7)->where('quantity_current', '>', 0)->count(),
            'warning_batches' => Batch::expiring(30)->where('quantity_current', '>', 0)->count(),
            'total_active_quantity' => Batch::where('status', 'active')->sum('quantity_current'),
        ];
    }
}
