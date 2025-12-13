<?php

namespace Tests\Feature\Checkout;

use App\Livewire\Store\CheckoutPage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticatedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = Customer::factory()->create([
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
     * Test authenticated checkout creates order with customer_id set
     */
    public function test_authenticated_checkout_links_order_to_user(): void
    {
        // Create cart for the customer
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->customer, 'customer');

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

        // Verify order was created with customer_id
        $order = Order::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($order, 'Order should be created with customer_id');
        $this->assertEquals($this->customer->id, $order->customer_id);
    }

    /**
     * Test authenticated user creating new address doesn't fail
     */
    public function test_authenticated_user_can_create_new_shipping_address(): void
    {
        // Create cart for the user
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->customer);

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
        $address = ShippingAddress::where('customer_id', $this->customer->id)->first();
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
            'customer_id' => $this->customer->id,
            'full_name' => 'Saved Name',
            'email' => 'saved@example.com',
            'phone' => '01234567890',
            'governorate' => 'Giza',
            'city' => 'Dokki',
            'street_address' => '456 Saved Street',
            'is_default' => true,
        ]);

        // Create cart for the user
        $cart = Cart::create(['customer_id' => $this->customer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($this->customer);

        Livewire::test(CheckoutPage::class)
            ->set('selectedAddressId', $address->id)
            ->set('showAddressForm', false)
            ->set('paymentMethod', 'cod')
            ->call('placeOrder');

        // Verify order was created with customer_id and shipping address
        $order = Order::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($order, 'Order should be created');
        $this->assertEquals($this->customer->id, $order->customer_id);
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
            'customer_id' => $this->customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'shipping_cost' => 50,
            'total' => 150,
        ]);

        $this->actingAs($this->customer);

        $response = $this->get('/account/orders');
        $response->assertStatus(200);
        $response->assertSee('VLT-TEST123');
    }
}

