<?php

namespace Tests\Feature\Checkout;

use App\Livewire\Store\CheckoutPage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticatedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '01234567890',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    /**
     * Test authenticated checkout creates order with user_id set
     */
    public function test_authenticated_checkout_links_order_to_user(): void
    {
        // Create cart for the user
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->user);

        Livewire::test(CheckoutPage::class)
            ->set('showAddressForm', true)
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'test@example.com')
            ->set('phone', '01234567890')
            ->set('governorate', 'Cairo')
            ->set('city', 'Nasr City')
            ->set('address_details', '123 Test Street')
            ->set('paymentMethod', 'cod')
            ->call('placeOrder');

        // Verify order was created with user_id
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order, 'Order should be created with user_id');
        $this->assertEquals($this->user->id, $order->user_id);
    }

    /**
     * Test authenticated user creating new address doesn't fail
     */
    public function test_authenticated_user_can_create_new_shipping_address(): void
    {
        // Create cart for the user
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->user);

        Livewire::test(CheckoutPage::class)
            ->set('showAddressForm', true)
            ->set('first_name', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'test@example.com')
            ->set('phone', '01234567890')
            ->set('governorate', 'Cairo')
            ->set('city', 'Nasr City')
            ->set('address_details', '123 Test Street')
            ->set('paymentMethod', 'cod')
            ->call('placeOrder');

        // Verify shipping address was created
        $address = ShippingAddress::where('user_id', $this->user->id)->first();
        $this->assertNotNull($address, 'Shipping address should be created');
        $this->assertEquals('John Doe', $address->full_name);
        $this->assertEquals('test@example.com', $address->email);
    }

    /**
     * Test authenticated user with existing address can checkout
     */
    public function test_authenticated_user_can_use_saved_address(): void
    {
        // Create saved address for user
        $address = ShippingAddress::create([
            'user_id' => $this->user->id,
            'full_name' => 'Saved Name',
            'email' => 'saved@example.com',
            'phone' => '01234567890',
            'governorate' => 'Giza',
            'city' => 'Dokki',
            'street_address' => '456 Saved Street',
            'is_default' => true,
        ]);

        // Create cart for the user
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->user);

        Livewire::test(CheckoutPage::class)
            ->set('selectedAddressId', $address->id)
            ->set('showAddressForm', false)
            ->set('paymentMethod', 'cod')
            ->call('placeOrder');

        // Verify order was created with user_id and shipping address
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order, 'Order should be created');
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals($address->id, $order->shipping_address_id);
    }

    /**
     * Test orders appear in user's account
     */
    public function test_user_orders_appear_in_account(): void
    {
        // Create order linked to user
        $order = Order::create([
            'order_number' => 'VLT-TEST123',
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 50,
            'total' => 150,
        ]);

        $this->actingAs($this->user);

        $response = $this->get('/account/orders');
        $response->assertStatus(200);
        $response->assertSee('VLT-TEST123');
    }
}
