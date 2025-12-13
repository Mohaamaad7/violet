<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class ReturnResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
        
        // Seed return policy settings
        $this->artisan('db:seed', ['--class' => 'ReturnPolicySettingsSeeder']);
    }

    /** @test */
    public function admin_can_view_order_returns_list()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'type' => 'rejection',
            'status' => 'pending',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.index'));

        $response->assertStatus(200);
        $response->assertSee($return->return_number);
    }

    /** @test */
    public function admin_can_view_return_details()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-001',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'type' => 'return_after_delivery',
            'status' => 'pending',
        ]);

        $return->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'reason' => 'Defective',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.view', $return));

        $response->assertStatus(200);
        $response->assertSee($return->return_number);
        $response->assertSee('Test Product');
    }

    /** @test */
    public function admin_can_approve_pending_return()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-002',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'type' => 'rejection',
            'status' => 'pending',
        ]);

        $return->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'reason' => 'Changed my mind',
        ]);

        $this->post(route('filament.admin.resources.order-returns.approve', $return));

        $this->assertDatabaseHas('order_returns', [
            'id' => $return->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_pending_return()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-003',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'type' => 'rejection',
            'status' => 'pending',
        ]);

        $return->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'reason' => 'Changed my mind',
        ]);

        $this->post(route('filament.admin.resources.order-returns.reject', $return), [
            'rejection_reason' => 'Out of policy',
        ]);

        $this->assertDatabaseHas('order_returns', [
            'id' => $return->id,
            'status' => 'rejected',
            'rejection_reason' => 'Out of policy',
        ]);
    }

    /** @test */
    public function admin_can_process_approved_return()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-004',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'type' => 'return_after_delivery',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
        ]);

        $returnItem = $return->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'reason' => 'Defective',
        ]);

        $this->post(route('filament.admin.resources.order-returns.process', $return), [
            'items' => [
                $orderItem->id => [
                    'received_quantity' => 2,
                    'condition' => 'good',
                    'notes' => 'All items received in good condition',
                ],
            ],
        ]);

        $this->assertDatabaseHas('order_returns', [
            'id' => $return->id,
            'status' => 'completed',
        ]);

        // Check stock was restored
        $this->assertEquals(102, $product->fresh()->stock);
    }

    /** @test */
    public function approve_action_not_visible_for_non_pending_returns()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'status' => 'approved',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.view', $return));

        $response->assertStatus(200);
        $response->assertDontSee('Approve Return');
    }

    /** @test */
    public function reject_action_not_visible_for_non_pending_returns()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'status' => 'approved',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.view', $return));

        $response->assertStatus(200);
        $response->assertDontSee('Reject Return');
    }

    /** @test */
    public function process_action_not_visible_for_non_approved_returns()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.view', $return));

        $response->assertStatus(200);
        $response->assertDontSee('Process Return');
    }

    /** @test */
    public function customer_can_create_return_from_delivered_order()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(2),
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'product_sku' => 'TEST-005',
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);

        // Simulate customer creating return from frontend
        $returnService = app(\App\Services\ReturnService::class);
        
        $return = $returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $this->assertNotNull($return);
        $this->assertEquals($order->id, $return->order_id);
        $this->assertEquals('pending', $return->status);
    }

    /** @test */
    public function return_filters_work_correctly()
    {
        $customer = Customer::factory()->create();
        $order1 = Order::factory()->create(['customer_id' => $customer->id]);
        $order2 = Order::factory()->create(['customer_id' => $customer->id]);

        $pendingReturn = OrderReturn::factory()->create([
            'order_id' => $order1->id,
            'status' => 'pending',
        ]);

        $approvedReturn = OrderReturn::factory()->create([
            'order_id' => $order2->id,
            'status' => 'approved',
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.index', [
            'tableFilters' => ['status' => ['value' => 'pending']],
        ]));

        $response->assertStatus(200);
        $response->assertSee($pendingReturn->return_number);
    }

    /** @test */
    public function returns_table_shows_customer_name()
    {
        $customer = Customer::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $return = OrderReturn::factory()->create([
            'order_id' => $order->id,
        ]);

        $response = $this->get(route('filament.admin.resources.order-returns.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    /** @test */
    public function return_number_is_unique()
    {
        $customer = Customer::factory()->create();
        $order1 = Order::factory()->create(['customer_id' => $customer->id]);
        $order2 = Order::factory()->create(['customer_id' => $customer->id]);

        $return1 = OrderReturn::factory()->create(['order_id' => $order1->id]);
        $return2 = OrderReturn::factory()->create(['order_id' => $order2->id]);

        $this->assertNotEquals($return1->return_number, $return2->return_number);
    }

    /** @test */
    public function admin_cannot_create_returns_directly()
    {
        // Returns can only be created through ReturnService by customers
        $response = $this->get(route('filament.admin.resources.order-returns.create'));

        // Should either return 404 or redirect since create is disabled
        $this->assertContains($response->status(), [404, 302, 403]);
    }
}
