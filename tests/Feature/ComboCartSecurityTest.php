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
 * ComboCartSecurityTest
 *
 * Verifies that combo-locked cart items cannot have their quantities
 * modified via the CartService, preventing financial manipulation.
 */
class ComboCartSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;
    protected Customer $customer;
    protected Cart $cart;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);

        // Create a customer with a cart
        $this->customer = Customer::factory()->create();
        $this->cart = Cart::create([
            'customer_id' => $this->customer->id,
            'session_id'  => null,
        ]);

        $this->product = Product::factory()->create([
            'name'  => 'Test Product',
            'price' => 100.00,
            'stock' => 50,
        ]);
    }

    /**
     * Test: Quantity update on a combo-locked item MUST be rejected.
     */
    public function test_update_quantity_rejects_combo_items(): void
    {
        $comboItem = CartItem::create([
            'cart_id'              => $this->cart->id,
            'product_id'           => $this->product->id,
            'product_variant_id'   => null,
            'quantity'             => 1,
            'price'                => 65.00,  // proportionally discounted
            'original_price'       => 100.00,
            'combo_instance_uuid'  => 'test-uuid-1234',
        ]);

        // Authenticate as this customer
        $this->actingAs($this->customer, 'customer');

        $result = $this->cartService->updateQuantity($comboItem->id, 5);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('لا يمكن تعديل كمية منتجات العرض المجمع', $result['message']);

        // Verify quantity was NOT changed in the database
        $comboItem->refresh();
        $this->assertEquals(1, $comboItem->quantity);
    }

    /**
     * Test: Quantity update on a regular (non-combo) item MUST succeed.
     */
    public function test_update_quantity_allows_regular_items(): void
    {
        $regularItem = CartItem::create([
            'cart_id'              => $this->cart->id,
            'product_id'           => $this->product->id,
            'product_variant_id'   => null,
            'quantity'             => 1,
            'price'                => 100.00,
            'combo_instance_uuid'  => null,  // NOT a combo item
        ]);

        $this->actingAs($this->customer, 'customer');

        $result = $this->cartService->updateQuantity($regularItem->id, 3);

        $this->assertTrue($result['success']);

        $regularItem->refresh();
        $this->assertEquals(3, $regularItem->quantity);
    }

    /**
     * Test: Quantity update on an item NOT belonging to the current user MUST fail.
     */
    public function test_update_quantity_rejects_idor_attacks(): void
    {
        // Create another customer's cart with an item
        $otherCustomer = Customer::factory()->create();
        $otherCart = Cart::create([
            'customer_id' => $otherCustomer->id,
            'session_id'  => null,
        ]);
        $otherItem = CartItem::create([
            'cart_id'    => $otherCart->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'price'      => 100.00,
        ]);

        // Authenticate as the FIRST customer (NOT the owner)
        $this->actingAs($this->customer, 'customer');

        $result = $this->cartService->updateQuantity($otherItem->id, 10);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('غير موجود في السلة', $result['message']);

        // Verify the other customer's item was NOT changed
        $otherItem->refresh();
        $this->assertEquals(1, $otherItem->quantity);
    }

    /**
     * Test: addComboToCart correctly bypasses quantity merging.
     */
    public function test_add_combo_to_cart_does_not_merge_with_existing(): void
    {
        // First, add a regular item
        $this->actingAs($this->customer, 'customer');

        CartItem::create([
            'cart_id'    => $this->cart->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
            'price'      => 100.00,
        ]);

        // Now add a combo with the same product
        $result = $this->cartService->addComboToCart([
            [
                'product_id'     => $this->product->id,
                'variant_id'     => null,
                'quantity'       => 1,
                'price'          => 65.00,
                'original_price' => 100.00,
            ],
        ], 'combo-uuid-abc');

        $this->assertTrue($result['success']);

        // There should now be 2 separate cart_items for the same product
        $items = CartItem::where('cart_id', $this->cart->id)
            ->where('product_id', $this->product->id)
            ->get();

        $this->assertCount(2, $items);

        // One regular, one combo
        $this->assertCount(1, $items->whereNull('combo_instance_uuid'));
        $this->assertCount(1, $items->where('combo_instance_uuid', 'combo-uuid-abc'));
    }
}
