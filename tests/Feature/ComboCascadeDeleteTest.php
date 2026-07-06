<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ComboCascadeDeleteTest
 *
 * Verifies that deleting any item from a combo bundle triggers a
 * server-side cascade delete of ALL items sharing the same combo_instance_uuid,
 * and that IDOR ownership is strictly enforced on deletions.
 */
class ComboCascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;
    protected Customer $customer;
    protected Cart $cart;
    protected Product $productA;
    protected Product $productB;
    protected Product $productC;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);

        $this->customer = Customer::factory()->create();
        $this->cart = Cart::create([
            'customer_id' => $this->customer->id,
            'session_id'  => null,
        ]);

        $this->productA = Product::factory()->create(['name' => 'Product A', 'price' => 100, 'stock' => 50]);
        $this->productB = Product::factory()->create(['name' => 'Product B', 'price' => 80, 'stock' => 50]);
        $this->productC = Product::factory()->create(['name' => 'Product C', 'price' => 120, 'stock' => 50]);
    }

    /**
     * Test: Removing one combo item deletes ALL items in the same bundle.
     */
    public function test_cascade_delete_removes_entire_combo_bundle(): void
    {
        $uuid = 'cascade-test-uuid-1';

        $itemA = CartItem::create([
            'cart_id'             => $this->cart->id,
            'product_id'          => $this->productA->id,
            'quantity'            => 1,
            'price'               => 65.00,
            'original_price'      => 100.00,
            'combo_instance_uuid' => $uuid,
        ]);

        $itemB = CartItem::create([
            'cart_id'             => $this->cart->id,
            'product_id'          => $this->productB->id,
            'quantity'            => 1,
            'price'               => 52.00,
            'original_price'      => 80.00,
            'combo_instance_uuid' => $uuid,
        ]);

        $itemC = CartItem::create([
            'cart_id'             => $this->cart->id,
            'product_id'          => $this->productC->id,
            'quantity'            => 1,
            'price'               => 78.00,
            'original_price'      => 120.00,
            'combo_instance_uuid' => $uuid,
        ]);

        $this->actingAs($this->customer, 'customer');

        // Remove item A — should cascade delete B and C
        $result = $this->cartService->removeItem($itemA->id);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('عرض الكومبو', $result['message']);

        // ALL three items should be gone
        $this->assertDatabaseMissing('cart_items', ['id' => $itemA->id]);
        $this->assertDatabaseMissing('cart_items', ['id' => $itemB->id]);
        $this->assertDatabaseMissing('cart_items', ['id' => $itemC->id]);
    }

    /**
     * Test: Cascade delete does NOT affect other combo bundles or regular items.
     */
    public function test_cascade_delete_isolates_bundles(): void
    {
        $uuid1 = 'bundle-1';
        $uuid2 = 'bundle-2';

        // Bundle 1
        $bundle1Item = CartItem::create([
            'cart_id'             => $this->cart->id,
            'product_id'          => $this->productA->id,
            'quantity'            => 1,
            'price'               => 65.00,
            'original_price'      => 100.00,
            'combo_instance_uuid' => $uuid1,
        ]);

        // Bundle 2
        $bundle2Item = CartItem::create([
            'cart_id'             => $this->cart->id,
            'product_id'          => $this->productB->id,
            'quantity'            => 1,
            'price'               => 52.00,
            'original_price'      => 80.00,
            'combo_instance_uuid' => $uuid2,
        ]);

        // Regular item (no combo)
        $regularItem = CartItem::create([
            'cart_id'    => $this->cart->id,
            'product_id' => $this->productC->id,
            'quantity'   => 2,
            'price'      => 120.00,
        ]);

        $this->actingAs($this->customer, 'customer');

        // Delete bundle 1
        $this->cartService->removeItem($bundle1Item->id);

        // Bundle 1 should be gone
        $this->assertDatabaseMissing('cart_items', ['id' => $bundle1Item->id]);

        // Bundle 2 and regular item should STILL exist
        $this->assertDatabaseHas('cart_items', ['id' => $bundle2Item->id]);
        $this->assertDatabaseHas('cart_items', ['id' => $regularItem->id]);
    }

    /**
     * Test: Regular (non-combo) item deletion only removes that single item.
     */
    public function test_regular_item_delete_is_not_cascade(): void
    {
        $regularA = CartItem::create([
            'cart_id'    => $this->cart->id,
            'product_id' => $this->productA->id,
            'quantity'   => 1,
            'price'      => 100.00,
        ]);

        $regularB = CartItem::create([
            'cart_id'    => $this->cart->id,
            'product_id' => $this->productB->id,
            'quantity'   => 1,
            'price'      => 80.00,
        ]);

        $this->actingAs($this->customer, 'customer');

        $this->cartService->removeItem($regularA->id);

        $this->assertDatabaseMissing('cart_items', ['id' => $regularA->id]);
        $this->assertDatabaseHas('cart_items', ['id' => $regularB->id]);
    }

    /**
     * Test: IDOR — cannot delete items from another customer's cart.
     */
    public function test_idor_prevents_cross_customer_deletion(): void
    {
        $otherCustomer = Customer::factory()->create();
        $otherCart = Cart::create([
            'customer_id' => $otherCustomer->id,
            'session_id'  => null,
        ]);

        $otherItem = CartItem::create([
            'cart_id'             => $otherCart->id,
            'product_id'          => $this->productA->id,
            'quantity'            => 1,
            'price'               => 65.00,
            'original_price'      => 100.00,
            'combo_instance_uuid' => 'other-uuid',
        ]);

        // Authenticate as a DIFFERENT customer
        $this->actingAs($this->customer, 'customer');

        $result = $this->cartService->removeItem($otherItem->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('غير موجود', $result['message']);

        // The item should NOT be deleted
        $this->assertDatabaseHas('cart_items', ['id' => $otherItem->id]);
    }
}
