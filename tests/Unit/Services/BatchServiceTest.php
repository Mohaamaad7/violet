<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BatchService;
use App\Services\StockMovementService;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BatchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BatchService $service;
    protected Product $product;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BatchService::class);
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->product = Product::factory()->create(['stock' => 0]);
    }

    /** @test */
    public function it_creates_batch_and_records_stock_movement(): void
    {
        $data = [
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH-001',
            'quantity_initial' => 100,
            // quantity_current will be set automatically by the service
            'manufacturing_date' => now()->subMonths(1),
            'expiry_date' => now()->addMonths(6),
            'supplier' => 'Test Supplier',
            'notes' => 'Test batch'
        ];

        $batch = $this->service->createBatch($data);

        $this->assertInstanceOf(Batch::class, $batch);
        $this->assertEquals('BATCH-001', $batch->batch_number);
        $this->assertEquals(100, $batch->quantity_initial);
        $this->assertEquals(100, $batch->quantity_current); // Updated by stock movement
        $this->assertEquals('active', $batch->status);

        // Verify stock movement was recorded
        $movement = StockMovement::where('product_id', $this->product->id)
            ->where('batch_id', $batch->id)
            ->where('type', 'restock')
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals(100, $movement->quantity);

        // Verify product stock updated
        $this->assertEquals(100, $this->product->fresh()->stock);
    }

    /** @test */
    public function it_deducts_from_batch(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 50,
            'status' => 'active'
        ]);

        $result = $this->service->deductFromBatch($batch->id, 20, null, 'Test deduction');

        $this->assertEquals(30, $batch->fresh()->quantity_current);
        
        // Verify stock movement
        $movement = StockMovement::where('batch_id', $batch->id)
            ->where('type', 'sale')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-20, $movement->quantity);
    }

    /** @test */
    public function it_prevents_deduction_exceeding_available_quantity(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 10
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient quantity in batch');

        $this->service->deductFromBatch($batch->id, 20, null, 'Invalid deduction');
    }

    /** @test */
    public function it_adds_to_batch(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 50
        ]);

        $this->service->addToBatch($batch->id, 25, null, 'Return to batch');

        $this->assertEquals(75, $batch->fresh()->quantity_current);
    }

    /** @test */
    public function it_retrieves_expiring_batches(): void
    {
        // Batch expiring in 5 days
        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addDays(5),
            'status' => 'active'
        ]);

        // Batch expiring in 20 days
        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addDays(20),
            'status' => 'active'
        ]);

        // Already expired
        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->subDays(1),
            'status' => 'expired'
        ]);

        $expiringSoon = $this->service->getExpiringBatches(10);
        $this->assertCount(1, $expiringSoon);

        $expiringInMonth = $this->service->getExpiringBatches(30);
        $this->assertCount(2, $expiringInMonth);
    }

    /** @test */
    public function it_marks_batch_as_expired(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 30,
            'expiry_date' => now()->subDays(1),
            'status' => 'active'
        ]);

        $this->service->markAsExpired($batch->id, 'Auto-marked expired');

        $batch->refresh();
        $this->assertEquals('expired', $batch->status);
        $this->assertEquals(0, $batch->quantity_current);

        // Verify expired movement recorded
        $movement = StockMovement::where('batch_id', $batch->id)
            ->where('type', 'expired')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(-30, $movement->quantity);
    }

    /** @test */
    public function it_marks_batch_as_disposed(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 20,
            'status' => 'active'
        ]);

        $this->service->markAsDisposed($batch->id, 'Damaged goods');

        $batch->refresh();
        $this->assertEquals('disposed', $batch->status);
        $this->assertEquals(0, $batch->quantity_current);

        // Verify damaged movement recorded
        $movement = StockMovement::where('batch_id', $batch->id)
            ->where('type', 'damaged')
            ->first();
        $this->assertNotNull($movement);
    }

    /** @test */
    public function it_auto_marks_expired_batches(): void
    {
        // Create 2 expired batches
        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->subDays(1),
            'quantity_current' => 10,
            'status' => 'active'
        ]);

        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->subDays(5),
            'quantity_current' => 15,
            'status' => 'active'
        ]);

        // One not expired
        Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addDays(10),
            'status' => 'active'
        ]);

        $count = $this->service->autoMarkExpiredBatches();

        $this->assertEquals(2, $count);
        $this->assertEquals(2, Batch::where('status', 'expired')->count());
    }

    /** @test */
    public function it_retrieves_batch_stats(): void
    {
        Batch::factory()->create(['product_id' => $this->product->id, 'status' => 'active']);
        Batch::factory()->create(['product_id' => $this->product->id, 'status' => 'expired']);
        Batch::factory()->create(['product_id' => $this->product->id, 'status' => 'disposed']);

        $stats = $this->service->getBatchStats();

        $this->assertArrayHasKey('total_batches', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertEquals(3, $stats['total_batches']);
    }

    /** @test */
    public function batch_alert_level_accessor_works(): void
    {
        $expired = Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->subDays(1)
        ]);
        $this->assertEquals('expired', $expired->alert_level);

        $critical = Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addDays(5)
        ]);
        $this->assertEquals('critical', $critical->alert_level);

        $warning = Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addDays(20)
        ]);
        $this->assertEquals('warning', $warning->alert_level);

        $ok = Batch::factory()->create([
            'product_id' => $this->product->id,
            'expiry_date' => now()->addMonths(2)
        ]);
        $this->assertEquals('ok', $ok->alert_level);
    }
}
