<?php

namespace Tests\Feature\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockFlowTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected StockMovementService $stockMovementService;
    protected User $admin;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->stockMovementService = app(StockMovementService::class);

        // Create admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'status' => 'active',
        ]);

        // Create product with stock
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 100,
            'stock' => 50,
        ]);

        // Create order with item
        $this->order = Order::factory()->create([
            'status' => 'pending',
            'total' => 200,
            'stock_deducted_at' => null,
            'stock_restored_at' => null,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'quantity' => 2,
            'price' => 100,
            'subtotal' => 200,
        ]);
    }

    /**
     * Test: Order shipment deducts stock correctly
     *
     * @return void
     */
    public function test_order_shipment_deducts_stock(): void
    {
        // Arrange
        $initialStock = $this->product->stock; // 50
        $orderQuantity = 2;
        $expectedStock = $initialStock - $orderQuantity; // 48

        // Act: Change order status to 'shipped'
        $updatedOrder = $this->orderService->updateStatus(
            $this->order->id,
            'shipped'
        );

        // Assert: Product stock decreased
        $this->product->refresh();
        $this->assertEquals($expectedStock, $this->product->stock);

        // Assert: Order has stock_deducted_at timestamp
        $this->assertNotNull($updatedOrder->stock_deducted_at);

        // Assert: StockMovement record created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => -$orderQuantity,
            'reference_type' => Order::class,
            'reference_id' => $this->order->id,
        ]);
    }

    /**
     * Test: Order cancellation (after shipping) restores stock correctly
     *
     * @return void
     */
    public function test_order_cancellation_restores_stock(): void
    {
        // Arrange: First ship the order (deduct stock)
        $this->orderService->updateStatus($this->order->id, 'shipped');
        $this->product->refresh();
        $stockAfterShipment = $this->product->stock; // 48

        // Act: Change order status to 'cancelled'
        $updatedOrder = $this->orderService->updateStatus(
            $this->order->id,
            'cancelled',
            'سبب الإلغاء للاختبار'
        );

        // Assert: Product stock restored
        $this->product->refresh();
        $this->assertEquals(50, $this->product->stock); // Back to original

        // Assert: Order has stock_restored_at timestamp
        $this->assertNotNull($updatedOrder->stock_restored_at);

        // Assert: StockMovement record for restoration created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'return',
            'quantity' => 2, // Positive for restoration
            'reference_type' => Order::class,
            'reference_id' => $this->order->id,
        ]);
    }

    /**
     * Test: Insufficient stock prevents shipment
     *
     * @return void
     */
    public function test_insufficient_stock_prevents_shipment(): void
    {
        // Arrange: Set product stock lower than order quantity
        $this->product->update(['stock' => 1]); // Order needs 2, but only 1 available

        // Act: Attempt to ship the order
        $result = $this->orderService->deductStockForOrder($this->order);

        // Assert: Operation failed
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('المخزون غير كافي', $result['message']);

        // Assert: Stock not deducted
        $this->product->refresh();
        $this->assertEquals(1, $this->product->stock);

        // Assert: Order stock_deducted_at still null
        $this->order->refresh();
        $this->assertNull($this->order->stock_deducted_at);

        // Assert: No StockMovement record created
        $this->assertDatabaseMissing('stock_movements', [
            'product_id' => $this->product->id,
            'reference_type' => Order::class,
            'reference_id' => $this->order->id,
        ]);
    }

    /**
     * Test: Cannot deduct stock twice for same order
     *
     * @return void
     */
    public function test_cannot_deduct_stock_twice(): void
    {
        // Arrange: Ship order first time
        $shippedOrder = $this->orderService->updateStatus($this->order->id, 'shipped');
        $this->product->refresh();
        $stockAfterFirstShipment = $this->product->stock; // 48

        // Act: Attempt to deduct stock again (refresh order first to get stock_deducted_at)
        $result = $this->orderService->deductStockForOrder($shippedOrder);

        // Assert: Operation failed
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('تم خصم المخزون لهذا الطلب مسبقاً', $result['message']);

        // Assert: Stock unchanged
        $this->product->refresh();
        $this->assertEquals($stockAfterFirstShipment, $this->product->stock);
    }

    /**
     * Test: Cannot restore stock if never deducted
     *
     * @return void
     */
    public function test_cannot_restore_stock_if_never_deducted(): void
    {
        // Arrange: Order never shipped (pending status)
        $initialStock = $this->product->stock;

        // Act: Attempt to restore stock
        $result = $this->orderService->restockRejectedOrder($this->order);

        // Assert: Operation failed (method returns array with success=false)
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('لم يتم خصم المخزون لهذا الطلب من الأساس', $result['message']);

        // Assert: Stock unchanged
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    /**
     * Test: Cannot restore stock twice for same order
     *
     * @return void
     */
    public function test_cannot_restore_stock_twice(): void
    {
        // Arrange: Ship then cancel order
        $this->orderService->updateStatus($this->order->id, 'shipped');
        $cancelledOrder = $this->orderService->updateStatus($this->order->id, 'cancelled', 'سبب الإلغاء');
        $this->product->refresh();
        $stockAfterRestore = $this->product->stock; // 50

        // Act: Attempt to restore stock again
        $result = $this->orderService->restockRejectedOrder($cancelledOrder);

        // Assert: Operation failed
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('تم إرجاع المخزون لهذا الطلب مسبقاً', $result['message']);

        // Assert: Stock unchanged
        $this->product->refresh();
        $this->assertEquals($stockAfterRestore, $this->product->stock);
    }

    /**
     * Test: Validation correctly identifies insufficient stock
     *
     * @return void
     */
    public function test_validation_identifies_insufficient_stock(): void
    {
        // Arrange: Set stock to 1 (order needs 2)
        $this->product->update(['stock' => 1]);

        // Act: Validate stock for shipment
        $result = $this->orderService->validateStockForShipment($this->order);

        // Assert: Validation failed
        $this->assertFalse($result['canShip']);
        $this->assertCount(1, $result['issues']);
        $this->assertEquals($this->product->name, $result['issues'][0]['product']);
        $this->assertEquals(2, $result['issues'][0]['required']);
        $this->assertEquals(1, $result['issues'][0]['available']);
    }

    /**
     * Test: Validation passes when stock is sufficient
     *
     * @return void
     */
    public function test_validation_passes_with_sufficient_stock(): void
    {
        // Arrange: Product has stock = 50, order needs 2
        // (Already set in setUp)

        // Act: Validate stock for shipment
        $result = $this->orderService->validateStockForShipment($this->order);

        // Assert: Validation passed
        $this->assertTrue($result['canShip']);
        $this->assertEmpty($result['issues']);
    }

    /**
     * Test: Stock deduction with multiple products
     *
     * @return void
     */
    public function test_stock_deduction_with_multiple_products(): void
    {
        // Arrange: Add second product to order
        $product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'sku' => 'TEST-002',
            'price' => 50,
            'stock' => 30,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'product_sku' => $product2->sku,
            'quantity' => 3,
            'price' => 50,
            'subtotal' => 150,
        ]);

        // Act: Ship order
        $this->orderService->updateStatus($this->order->id, 'shipped', 'تم الشحن', null);

        // Assert: Both products stock deducted
        $this->product->refresh();
        $this->assertEquals(48, $this->product->stock); // 50 - 2

        $product2->refresh();
        $this->assertEquals(27, $product2->stock); // 30 - 3

        // Assert: Two StockMovement records created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'quantity' => -2,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product2->id,
            'quantity' => -3,
        ]);
    }

    /**
     * Test: Partial insufficient stock blocks entire shipment
     *
     * @return void
     */
    public function test_partial_insufficient_stock_blocks_shipment(): void
    {
        // Arrange: Product 1 has sufficient stock, Product 2 doesn't
        $product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'sku' => 'TEST-002',
            'price' => 50,
            'stock' => 1, // Insufficient (order needs 3)
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'product_sku' => $product2->sku,
            'quantity' => 3,
            'price' => 50,
            'subtotal' => 150,
        ]);

        // Act: Attempt to ship order
        $result = $this->orderService->deductStockForOrder($this->order);

        // Assert: Operation failed
        $this->assertFalse($result['success']);

        // Assert: NO stock deducted for ANY product (atomic operation)
        $this->product->refresh();
        $this->assertEquals(50, $this->product->stock); // Unchanged

        $product2->refresh();
        $this->assertEquals(1, $product2->stock); // Unchanged
    }

    /**
     * Test: Status flow from pending to delivered with stock deduction
     *
     * @return void
     */
    public function test_complete_status_flow_with_stock(): void
    {
        // Act & Assert: pending → processing (no stock change)
        $processingOrder = $this->orderService->updateStatus($this->order->id, 'processing');
        $this->product->refresh();
        $this->assertEquals(50, $this->product->stock);
        $this->assertNull($processingOrder->stock_deducted_at);

        // Act & Assert: processing → shipped (stock deducted)
        $shippedOrder = $this->orderService->updateStatus($this->order->id, 'shipped');
        $this->product->refresh();
        $this->assertEquals(48, $this->product->stock);
        $this->assertNotNull($shippedOrder->stock_deducted_at);

        // Act & Assert: shipped → delivered (no stock change)
        $deliveredOrder = $this->orderService->updateStatus($this->order->id, 'delivered');
        $this->product->refresh();
        $this->assertEquals(48, $this->product->stock); // Still 48
        $this->assertNotNull($deliveredOrder->stock_deducted_at);
        $this->assertNull($deliveredOrder->stock_restored_at);
    }
}
