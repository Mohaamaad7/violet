<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StockMovementService;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockMovementService $service;
    protected Product $product;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockMovementService::class);
        
        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create test product with stock
        $this->product = Product::factory()->create([
            'stock' => 100,
            'sku' => 'TEST-SKU-001'
        ]);
    }

    /** @test */
    public function it_records_stock_movement_and_updates_product_stock(): void
    {
        $initialStock = $this->product->stock;
        
        $movement = $this->service->recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: -10,
            reference: null,
            notes: 'Test sale'
        );

        $this->assertInstanceOf(StockMovement::class, $movement);
        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals('sale', $movement->type);
        $this->assertEquals(-10, $movement->quantity);
        $this->assertEquals($initialStock, $movement->stock_before);
        $this->assertEquals($initialStock - 10, $movement->stock_after);
        $this->assertEquals($this->user->id, $movement->created_by);

        // Verify product stock updated
        $this->assertEquals(90, $this->product->fresh()->stock);
    }

    /** @test */
    public function it_records_movement_with_batch(): void
    {
        $batch = Batch::factory()->create([
            'product_id' => $this->product->id,
            'quantity_current' => 50
        ]);

        $movement = $this->service->recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: -5,
            reference: null,
            notes: 'Batch sale',
            batchId: $batch->id
        );

        $this->assertEquals($batch->id, $movement->batch_id);
        $this->assertEquals(45, $batch->fresh()->quantity_current);
    }

    /** @test */
    public function it_records_polymorphic_reference(): void
    {
        $order = \App\Models\Order::factory()->create();

        $movement = $this->service->recordMovement(
            productId: $this->product->id,
            type: 'sale',
            quantity: -3,
            reference: $order,
            notes: 'Order sale'
        );

        $this->assertEquals('App\Models\Order', $movement->reference_type);
        $this->assertEquals($order->id, $movement->reference_id);
        $this->assertInstanceOf(\App\Models\Order::class, $movement->reference);
    }

    /** @test */
    public function it_retrieves_movement_history_with_filters(): void
    {
        // Create various movements
        $this->service->recordMovement($this->product->id, 'restock', 50, null, 'Restock 1');
        $this->service->recordMovement($this->product->id, 'sale', -10, null, 'Sale 1');
        $this->service->recordMovement($this->product->id, 'return', 5, null, 'Return 1');

        $history = $this->service->getMovementHistory($this->product->id);
        $this->assertCount(3, $history);

        // Filter by type
        $sales = $this->service->getMovementHistory($this->product->id, ['type' => 'sale']);
        $this->assertCount(1, $sales);
        $this->assertEquals('sale', $sales->first()->type);
    }

    /** @test */
    public function it_calculates_stock_change_over_period(): void
    {
        $startDate = now()->subDays(10);
        $endDate = now();

        $this->service->recordMovement($this->product->id, 'restock', 50, null, 'Restock');
        $this->service->recordMovement($this->product->id, 'sale', -20, null, 'Sale');
        $this->service->recordMovement($this->product->id, 'return', 5, null, 'Return');

        $summary = $this->service->calculateStockChange(
            $this->product->id,
            $startDate,
            $endDate
        );

        $this->assertEquals(50, $summary['restock']);
        $this->assertEquals(-20, $summary['sale']);
        $this->assertEquals(5, $summary['return']);
        $this->assertEquals(35, $summary['net_change']);
    }

    /** @test */
    public function deduct_stock_helper_creates_negative_movement(): void
    {
        $movement = $this->service->deductStock(
            $this->product->id,
            10,
            null,
            'Test deduction'
        );

        $this->assertEquals(-10, $movement->quantity);
        $this->assertEquals(90, $this->product->fresh()->stock);
    }

    /** @test */
    public function add_stock_helper_creates_positive_movement(): void
    {
        $movement = $this->service->addStock(
            $this->product->id,
            25,
            'restock',
            null,
            'Test addition'
        );

        $this->assertEquals(25, $movement->quantity);
        $this->assertEquals(125, $this->product->fresh()->stock);
    }

    /** @test */
    public function it_gets_summary_stats(): void
    {
        $this->service->recordMovement($this->product->id, 'restock', 100, null, 'R1');
        $this->service->recordMovement($this->product->id, 'sale', -50, null, 'S1');
        $this->service->recordMovement($this->product->id, 'return', 10, null, 'Ret1');

        $stats = $this->service->getSummaryStats();

        $this->assertArrayHasKey('total_movements', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertEquals(3, $stats['total_movements']);
    }
}
