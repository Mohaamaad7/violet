<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ReturnService $returnService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->returnService = app(ReturnService::class);
        
        // Seed return policy settings
        $this->artisan('db:seed', ['--class' => 'ReturnPolicySettingsSeeder']);
    }

    /** @test */
    public function it_validates_return_window_based_on_settings()
    {
        // Set return window to 7 days
        Setting::set('return_window_days', '7', 'integer', 'returns');

        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(10), // 10 days ago (exceeds 7-day window)
        ]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Return window has expired');

        $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Defective product',
            'items' => [$order->items->first()->id],
        ]);
    }

    /** @test */
    public function it_allows_returns_within_configured_window()
    {
        // Set return window to 14 days
        Setting::set('return_window_days', '14', 'integer', 'returns');

        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(7), // 7 days ago (within 14-day window)
        ]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Product not as described',
            'items' => [$order->items->first()->id],
        ]);

        $this->assertNotNull($return);
        $this->assertEquals('pending', $return->status);
    }

    /** @test */
    public function it_auto_approves_rejections_when_setting_enabled()
    {
        // Enable auto-approval for rejections
        Setting::set('auto_approve_rejections', '1', 'boolean', 'returns');

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
        
        $order->items()->create([
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
            'items' => [$order->items->first()->id],
        ]);

        $this->assertEquals('approved', $return->status);
        $this->assertNotNull($return->approved_at);
    }

    /** @test */
    public function it_does_not_auto_approve_rejections_when_setting_disabled()
    {
        // Disable auto-approval for rejections
        Setting::set('auto_approve_rejections', '0', 'boolean', 'returns');

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
        
        $order->items()->create([
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
            'items' => [$order->items->first()->id],
        ]);

        $this->assertEquals('pending', $return->status);
        $this->assertNull($return->approved_at);
    }

    /** @test */
    public function it_does_not_auto_approve_non_rejection_returns()
    {
        // Enable auto-approval for rejections
        Setting::set('auto_approve_rejections', '1', 'boolean', 'returns');

        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(2),
        ]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        // Create return_after_delivery instead of rejection
        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Defective item',
            'items' => [$order->items->first()->id],
        ]);

        $this->assertEquals('pending', $return->status);
        $this->assertNull($return->approved_at);
    }

    /** @test */
    public function it_uses_config_fallback_when_setting_not_in_database()
    {
        // Delete the setting from database
        Setting::where('key', 'return_window_days')->delete();

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
        
        $order->items()->create([
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
            'items' => [$order->items->first()->id],
        ]);

        $this->assertNotNull($return);
        $this->assertEquals('pending', $return->status);
    }

    /** @test */
    public function it_allows_partial_returns_when_enabled()
    {
        // Enable partial returns
        Setting::set('allow_partial_returns', '1', 'boolean', 'returns');

        $customer = Customer::factory()->create();
        $product1 = Product::factory()->create(['stock' => 100]);
        $product2 = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now()->subDays(2),
        ]);

        $price1 = $product1->final_price;
        $productName1 = $product1->name;
        $productSku1 = $product1->sku;
        
        $item1 = $order->items()->create([
            'product_id' => $product1->id,
            'product_name' => $productName1,
            'product_sku' => $productSku1,
            'quantity' => 2,
            'price' => $price1,
            'subtotal' => 2 * $price1,
        ]);

        $price2 = $product2->final_price;
        $productName2 = $product2->name;
        $productSku2 = $product2->sku;
        
        $item2 = $order->items()->create([
            'product_id' => $product2->id,
            'product_name' => $productName2,
            'product_sku' => $productSku2,
            'quantity' => 1,
            'price' => $price2,
            'subtotal' => $price2,
        ]);

        // Return only item1
        $return = $this->returnService->createReturnRequest($order->id, [
            'type' => 'return_after_delivery',
            'reason' => 'Defective item',
            'items' => [$item1->id],
        ]);

        $this->assertNotNull($return);
        $this->assertCount(1, $return->items);
        $this->assertEquals($item1->id, $return->items->first()->order_item_id);
    }

    /** @test */
    public function setting_helper_function_returns_correct_values()
    {
        Setting::set('test_key', 'test_value', 'string', 'test');

        $value = setting('test_key');
        
        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function setting_set_helper_function_creates_and_updates_settings()
    {
        // Create new setting
        $setting = setting_set('new_key', 'new_value', 'string', 'test');
        
        $this->assertEquals('new_value', $setting->value);
        
        // Update existing setting
        $updated = setting_set('new_key', 'updated_value', 'string', 'test');
        
        $this->assertEquals('updated_value', $updated->value);
        $this->assertEquals($setting->id, $updated->id);
    }

    /** @test */
    public function it_validates_order_status_before_creating_return()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'cancelled', // Cannot create return on cancelled order
        ]);

        $price = $product->final_price;
        $quantity = 2;
        $productName = $product->name;
        $productSku = $product->sku;
        
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $productName,
            'product_sku' => $productSku,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order cannot be rejected in current status');

        $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$order->items->first()->id],
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_return_requests()
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

        // Create first return
        $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Changed my mind',
            'items' => [$orderItem->id],
        ]);

        $this->expectException(\Exception::class);

        // Attempt to create duplicate return
        $this->returnService->createReturnRequest($order->id, [
            'type' => 'rejection',
            'reason' => 'Still changed my mind',
            'items' => [$orderItem->id],
        ]);
    }
}

