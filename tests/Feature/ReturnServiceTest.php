<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReturnService $returnService;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->returnService = app(ReturnService::class);
        $this->admin = User::factory()->create();
        
        // Seed return policy settings
        $this->artisan('db:seed', ['--class' => 'ReturnPolicySettingsSeeder']);
    }

    /** @test */
    public function it_creates_return_request_successfully()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $this->assertNotNull($return);
        $this->assertEquals('pending', $return->status);
        $this->assertEquals($order->id, $return->order_id);
        $this->assertStringStartsWith('RET-', $return->return_number);
    }

    /** @test */
    public function it_approves_return_request()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $approved = $this->returnService->approveReturn($return->id, $this->admin->id);

        $this->assertEquals('approved', $approved->status);
        $this->assertNotNull($approved->approved_at);
        $this->assertEquals($this->admin->id, $approved->approved_by);
    }

    /** @test */
    public function it_rejects_return_request()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $rejected = $this->returnService->rejectReturn($return->id, $this->admin->id, 'Out of policy');

        $this->assertEquals('rejected', $rejected->status);
        $this->assertNotNull($rejected->rejected_at);
        $this->assertEquals($this->admin->id, $rejected->rejected_by);
        $this->assertEquals('Out of policy', $rejected->admin_notes);
    }

    /** @test */
    public function it_processes_return_and_restocks_items()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
'total' => 200,
        ]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $this->returnService->approveReturn($return->id, $this->admin->id);

        $processed = $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'good',
                'notes' => 'Items in perfect condition',
            ],
        ], $this->admin->id);

        $this->assertEquals('completed', $processed->status);
        $this->assertNotNull($processed->processed_at);
        
        // Check stock was restored
        $this->assertEquals(102, $product->fresh()->stock);
    }

    /** @test */
    public function it_does_not_restock_damaged_items()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(2),
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-123',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Defective',
            'items' => [$orderItem->id],
        ]);

        $this->returnService->approveReturn($return->id, $this->admin->id);

        $processed = $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'damaged',
                'notes' => 'Items damaged',
            ],
        ], $this->admin->id);

        $this->assertEquals('completed', $processed->status);
        
        // Check stock was NOT restored for damaged items
        $this->assertEquals(100, $product->fresh()->stock);
    }

    /** @test */
    public function it_processes_partial_returns_correctly()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(2),
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-456',
            'quantity' => 3,
            'price' => 100,
            'subtotal' => 300,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Partial defect',
            'items' => [$orderItem->id],
        ]);

        $this->returnService->approveReturn($return->id, $this->admin->id);

        // Return only 2 out of 3 items
        $processed = $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'good',
                'notes' => 'Received 2 items',
            ],
        ], $this->admin->id);

        $this->assertEquals('completed', $processed->status);
        
        // Check stock increased by 2 only
        $this->assertEquals(102, $product->fresh()->stock);
    }

    /** @test */
    public function it_generates_unique_return_numbers()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $orderItem1 = $order1->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Product 1',
            'product_sku' => 'TEST-001',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $orderItem2 = $order2->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Product 2',
            'product_sku' => 'TEST-002',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return1 = $this->returnService->createReturnRequest($order1->id, [
            'type' => 'rejection',
            'reason' => 'Reason 1',
            'items' => [$orderItem1->id],
        ]);

        $return2 = $this->returnService->createReturnRequest($order2->id, [
            'type' => 'rejection',
            'reason' => 'Reason 2',
            'items' => [$orderItem2->id],
        ]);

        $this->assertNotEquals($return1->return_number, $return2->return_number);
    }

    /** @test */
    public function it_prevents_processing_non_approved_returns()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-555',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        // Don't approve, try to process directly
        $this->expectException(\Exception::class);

        $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'good',
                'notes' => 'Received',
            ],
        ], $this->admin->id);
    }

    /** @test */
    public function it_prevents_double_processing()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-999',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $this->returnService->approveReturn($return->id, $this->admin->id);

        // Process first time
        $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'good',
                'notes' => 'Received',
            ],
        ], $this->admin->id);

        // Try to process again
        $this->expectException(\Exception::class);

        $this->returnService->processReturn($return->id, [
            $orderItem->id => [
                'received_quantity' => 2,
                'condition' => 'good',
                'notes' => 'Received again',
            ],
        ], $this->admin->id);
    }
}


