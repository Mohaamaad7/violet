<?php

namespace Tests\Feature\Account;

use App\Livewire\Store\Account\Dashboard;
use App\Livewire\Store\Account\Profile;
use App\Livewire\Store\Account\Addresses;
use App\Livewire\Store\Account\Orders;
use App\Livewire\Store\Account\OrderDetails;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Customer $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = Customer::factory()->create();
        $this->otherCustomer = Customer::factory()->create();
    }

    /** @test */
    public function guest_cannot_access_account_pages(): void
    {
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->get(route('account.profile'))->assertRedirect(route('login'));
        $this->get(route('account.addresses'))->assertRedirect(route('login'));
        $this->get(route('account.orders'))->assertRedirect(route('login'));
    }

    /** @test */
    public function customer_can_view_dashboard(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSeeLivewire(Dashboard::class);
    }

    /** @test */
    public function dashboard_shows_order_statistics(): void
    {
        // Create some orders for customer
        $address = ShippingAddress::factory()->create(['customer_id' => $this->customer->id]);
        
        Order::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
            'shipping_address_id' => $address->id,
        ]);
        
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
            'shipping_address_id' => $address->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Dashboard::class)
            ->assertSee('4') // total orders
            ->assertSee('1'); // pending orders
    }

    /** @test */
    public function customer_can_view_profile(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get(route('account.profile'))
            ->assertOk()
            ->assertSeeLivewire(Profile::class);
    }

    /** @test */
    public function customer_can_update_profile(): void
    {
        Livewire::actingAs($this->customer, 'customer')
            ->test(Profile::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('phone', '01234567890')
            ->call('updateProfile')
            ->assertDispatched('show-toast');

        $this->customer->refresh();
        $this->assertEquals('Updated Name', $this->customer->name);
        $this->assertEquals('updated@example.com', $this->customer->email);
        $this->assertEquals('01234567890', $this->customer->phone);
    }

    /** @test */
    public function customer_can_change_password(): void
    {
        $this->customer->update(['password' => bcrypt('oldpassword')]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Profile::class)
            ->call('togglePasswordForm')
            ->set('current_password', 'oldpassword')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('updatePassword')
            ->assertDispatched('show-toast')
            ->assertSet('showPasswordForm', false);
    }

    /** @test */
    public function customer_can_view_addresses(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get(route('account.addresses'))
            ->assertOk()
            ->assertSeeLivewire(Addresses::class);
    }

    /** @test */
    public function customer_can_add_address(): void
    {
        Livewire::actingAs($this->customer, 'customer')
            ->test(Addresses::class)
            ->call('openForm')
            ->set('full_name', 'John Doe')
            ->set('phone', '01234567890')
            ->set('governorate', 'Cairo')
            ->set('city', 'Nasr City')
            ->set('street_address', '15 Test Street')
            ->set('is_default', true)
            ->call('save')
            ->assertDispatched('show-toast');

        $this->assertDatabaseHas('shipping_addresses', [
            'customer_id' => $this->customer->id,
            'full_name' => 'John Doe',
            'governorate' => 'Cairo',
            'city' => 'Nasr City',
            'is_default' => true,
        ]);
    }

    /** @test */
    public function customer_can_edit_own_address(): void
    {
        $address = ShippingAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'full_name' => 'Original Name',
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Addresses::class)
            ->call('openForm', $address->id)
            ->assertSet('full_name', 'Original Name')
            ->set('full_name', 'Updated Name')
            ->call('save')
            ->assertDispatched('show-toast');

        $this->assertDatabaseHas('shipping_addresses', [
            'id' => $address->id,
            'full_name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function customer_cannot_edit_other_users_address(): void
    {
        $otherAddress = ShippingAddress::factory()->create([
            'customer_id' => $this->otherCustomer->id,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        Livewire::actingAs($this->customer, 'customer')
            ->test(Addresses::class)
            ->call('openForm', $otherAddress->id);
    }

    /** @test */
    public function customer_can_delete_own_address(): void
    {
        $address = ShippingAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Addresses::class)
            ->call('confirmDelete', $address->id)
            ->call('delete')
            ->assertDispatched('show-toast');

        $this->assertDatabaseMissing('shipping_addresses', [
            'id' => $address->id,
        ]);
    }

    /** @test */
    public function customer_can_set_default_address(): void
    {
        $address1 = ShippingAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'is_default' => true,
        ]);
        
        $address2 = ShippingAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'is_default' => false,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Addresses::class)
            ->call('setDefault', $address2->id)
            ->assertDispatched('show-toast');

        $address1->refresh();
        $address2->refresh();
        
        $this->assertFalse($address1->is_default);
        $this->assertTrue($address2->is_default);
    }

    /** @test */
    public function customer_can_view_orders(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get(route('account.orders'))
            ->assertOk()
            ->assertSeeLivewire(Orders::class);
    }

    /** @test */
    public function customer_only_sees_own_orders(): void
    {
        $address = ShippingAddress::factory()->create(['customer_id' => $this->customer->id]);
        $otherAddress = ShippingAddress::factory()->create(['customer_id' => $this->otherCustomer->id]);
        
        $myOrder = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-MY-001',
            'shipping_address_id' => $address->id,
        ]);
        
        $otherOrder = Order::factory()->create([
            'customer_id' => $this->otherCustomer->id,
            'order_number' => 'ORD-OTHER-001',
            'shipping_address_id' => $otherAddress->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Orders::class)
            ->assertSee('ORD-MY-001')
            ->assertDontSee('ORD-OTHER-001');
    }

    /** @test */
    public function customer_can_filter_orders_by_status(): void
    {
        $address = ShippingAddress::factory()->create(['customer_id' => $this->customer->id]);
        
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
            'order_number' => 'ORD-PENDING-001',
            'shipping_address_id' => $address->id,
        ]);
        
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
            'order_number' => 'ORD-DELIVERED-001',
            'shipping_address_id' => $address->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(Orders::class)
            ->set('status', 'pending')
            ->assertSee('ORD-PENDING-001')
            ->assertDontSee('ORD-DELIVERED-001');
    }

    /** @test */
    public function customer_can_view_own_order_details(): void
    {
        $address = ShippingAddress::factory()->create(['customer_id' => $this->customer->id]);
        
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
        ]);

        $this->actingAs($this->customer, 'customer')
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSeeLivewire(OrderDetails::class);
    }

    /** @test */
    public function customer_cannot_view_other_users_order(): void
    {
        $otherAddress = ShippingAddress::factory()->create(['customer_id' => $this->otherCustomer->id]);
        
        $otherOrder = Order::factory()->create([
            'customer_id' => $this->otherCustomer->id,
            'shipping_address_id' => $otherAddress->id,
        ]);

        $this->actingAs($this->customer, 'customer')
            ->get(route('account.orders.show', $otherOrder))
            ->assertForbidden();
    }

    /** @test */
    public function order_details_shows_all_order_info(): void
    {
        $address = ShippingAddress::factory()->create(['customer_id' => $this->customer->id]);
        $product = Product::factory()->create(['name' => 'Test Product']);
        
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-123',
            'total' => 250.00,
            'status' => 'processing',
            'shipping_address_id' => $address->id,
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100.00,
            'subtotal' => 200.00,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(OrderDetails::class, ['order' => $order])
            ->assertSee('ORD-TEST-123')
            ->assertSee('Test Product')
            ->assertSee('250.00');
    }
}

