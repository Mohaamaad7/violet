<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ReturnService;
use App\Services\StockMovementService;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReturnService $service;
    protected Order $order;
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReturnService::class);
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        $this->product = Product::factory()->create(['stock' => 100]);
        
        $this->order = Order::factory()->create([
            'status' => 'delivered',
            'user_id' => $this->user->id
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 250
        ]);
    }

    /** @test */
    public function it_creates_return_request_successfully(): void
    {
        $data = [
            'type' => 'return_after_delivery',
            'reason' => 'defective',
            'customer_notes' => 'Product not working',
            'items' => [
                [
                    'order_item_id' => $this->order->items->first()->id,
                    'quantity' => 1,
                    'reason' => 'Defective product'
                ]
            ]
        ];

        $return = $this->service->createReturnRequest($this->order->id, $data);

        $this->assertInstanceOf(OrderReturn::class, $return);
        $this->assertEquals($this->order->id, $return->order_id);
        $this->assertEquals('return_after_delivery', $return->type);
        $this->assertEquals('pending', $return->status);
        $this->assertStringStartsWith('RET-', $return->return_number);
        $this->assertCount(1, $return->items);

        // Verify order return_status updated
        $this->assertEquals('requested', $this->order->fresh()->return_status);
    }

    /** @test */
    public function it_prevents_duplicate_return_requests(): void
    {
        // Create first return
        $data = [
            'type' => 'return_after_delivery',
            'reason' => 'defective',
            'items' => [
                ['order_item_id' => $this->order->items->first()->id, 'quantity' => 1]
            ]
        ];
        $this->service->createReturnRequest($this->order->id, $data);

        // Try to create second return
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already has a pending or approved return request');

        $this->service->createReturnRequest($this->order->id, $data);
    }

    /** @test */
    public function it_validates_return_window(): void
    {
        // Create old order (> 14 days)
        $oldOrder = Order::factory()->create([
            'status' => 'delivered',
            'user_id' => $this->user->id,
            'delivered_at' => now()->subDays(20)
        ]);

        OrderItem::factory()->create([
            'order_id' => $oldOrder->id,
            'product_id' => $this->product->id
        ]);

        $data = [
            'type' => 'return_after_delivery',
            'reason' => 'defective',
            'items' => [['order_item_id' => $oldOrder->items->first()->id, 'quantity' => 1]]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Return window has expired');

        $this->service->createReturnRequest($oldOrder->id, $data);
    }

    /** @test */
    public function it_approves_return_successfully(): void
    {
        $return = OrderReturn::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'pending'
        ]);

        $approved = $this->service->approveReturn(
            $return->id,
            $this->user->id,
            'Approved for processing'
        );

        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($this->user->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
        $this->assertEquals('Approved for processing', $approved->admin_notes);

        // Verify order status
        $this->assertEquals('approved', $this->order->fresh()->return_status);
    }

    /** @test */
    public function it_rejects_return_successfully(): void
    {
        $return = OrderReturn::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'pending'
        ]);

        $rejected = $this->service->rejectReturn(
            $return->id,
            $this->user->id,
            'Does not meet return policy'
        );

        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals('Does not meet return policy', $rejected->admin_notes);

        // Verify order return_status reset
        $this->assertEquals('none', $this->order->fresh()->return_status);
    }

    /** @test */
    public function it_processes_return_and_restocks_eligible_items(): void
    {
        $return = OrderReturn::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'approved',
            'type' => 'return_after_delivery'
        ]);

        $returnItem = ReturnItem::factory()->create([
            'return_id' => $return->id,
            'order_item_id' => $this->order->items->first()->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 250,
            'restocked' => false
        ]);

        $initialStock = $this->product->stock;

        // Process with good condition (should restock)
        $processed = $this->service->processReturn(
            $return->id,
            [
                $returnItem->id => [
                    'condition' => 'good',
                    'notes' => 'Item in perfect condition'
                ]
            ],
            $this->user->id
        );

        $this->assertEquals('completed', $processed->status);
        $this->assertEquals($this->user->id, $processed->completed_by);
        $this->assertNotNull($processed->completed_at);

        // Verify item restocked
        $returnItem->refresh();
        $this->assertTrue($returnItem->restocked);
        $this->assertNotNull($returnItem->restocked_at);
        $this->assertEquals('good', $returnItem->condition);

        // Verify stock increased
        $this->assertEquals($initialStock + 2, $this->product->fresh()->stock);

        // Verify order status
        $this->assertEquals('completed', $this->order->fresh()->return_status);
    }

    /** @test */
    public function it_does_not_restock_damaged_items(): void
    {
        $return = OrderReturn::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'approved'
        ]);

        $returnItem = ReturnItem::factory()->create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'restocked' => false
        ]);

        $initialStock = $this->product->stock;

        // Process with damaged condition
        $this->service->processReturn(
            $return->id,
            [
                $returnItem->id => [
                    'condition' => 'damaged',
                    'notes' => 'Broken packaging'
                ]
            ],
            $this->user->id
        );

        $returnItem->refresh();
        $this->assertFalse($returnItem->restocked);
        $this->assertEquals('damaged', $returnItem->condition);

        // Stock should NOT increase
        $this->assertEquals($initialStock, $this->product->fresh()->stock);
    }

    /** @test */
    public function it_calculates_refund_amount(): void
    {
        $return = OrderReturn::factory()->create([
            'order_id' => $this->order->id,
            'status' => 'approved',
            'refund_amount' => 0
        ]);

        ReturnItem::factory()->create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 250 // Total: 500
        ]);

        $processed = $this->service->processReturn(
            $return->id,
            [
                $return->items->first()->id => ['condition' => 'good']
            ],
            $this->user->id
        );

        $this->assertEquals(500, $processed->refund_amount);
    }

    /** @test */
    public function it_retrieves_return_stats(): void
    {
        // Create various returns
        OrderReturn::factory()->create(['status' => 'pending', 'type' => 'rejection']);
        OrderReturn::factory()->create(['status' => 'approved', 'type' => 'return_after_delivery']);
        OrderReturn::factory()->create(['status' => 'completed', 'type' => 'return_after_delivery']);

        $stats = $this->service->getReturnStats();

        $this->assertArrayHasKey('total_returns', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertArrayHasKey('return_rate', $stats);
    }

    /** @test */
    public function return_number_is_unique_and_sequential(): void
    {
        $data = [
            'type' => 'return_after_delivery',
            'reason' => 'defective',
            'items' => [['order_item_id' => $this->order->items->first()->id, 'quantity' => 1]]
        ];

        $return1 = $this->service->createReturnRequest($this->order->id, $data);
        
        // Create another order and return
        $order2 = Order::factory()->create(['status' => 'delivered', 'user_id' => $this->user->id]);
        OrderItem::factory()->create(['order_id' => $order2->id, 'product_id' => $this->product->id]);
        
        $return2 = $this->service->createReturnRequest($order2->id, $data);

        $this->assertNotEquals($return1->return_number, $return2->return_number);
        $this->assertStringStartsWith('RET-', $return1->return_number);
        $this->assertStringStartsWith('RET-', $return2->return_number);
    }
}
