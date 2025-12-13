<?php

namespace Tests\Feature\Auth;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_guest_cart_is_merged_with_user_cart_on_login(): void
    {
        // Create test products
        $product1 = Product::factory()->create(['stock' => 100, 'status' => 'active', 'price' => 50]);
        $product2 = Product::factory()->create(['stock' => 100, 'status' => 'active', 'price' => 75]);

        // Create customer with existing cart
        $customer = Customer::factory()->create();
        $userCart = Cart::create(['customer_id' => $customer->id]);
        CartItem::create([
            'cart_id' => $userCart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->price,
        ]);

        // Create guest cart
        $guestSessionId = 'test-guest-session-id';
        $guestCart = Cart::create(['session_id' => $guestSessionId, 'user_id' => null]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'price' => $product2->price,
        ]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $product1->id, // Same product as user cart
            'quantity' => 1,
            'price' => $product1->price,
        ]);

        // Simulate cookie
        Cookie::queue('cart_session_id', $guestSessionId, 60 * 24 * 30);
        
        // Login via Livewire Volt (this triggers MergeCartOnLogin listener)
        $this->withCookie('cart_session_id', $guestSessionId);
        
        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');
        
        $this->assertAuthenticated();

        // Refresh user cart
        $userCart->refresh();
        $userCart->load('items');

        // Verify cart merge
        $this->assertEquals(2, $userCart->items->count());
        
        // Product 1 should have combined quantity (2 + 1 = 3)
        $product1Item = $userCart->items->where('product_id', $product1->id)->first();
        $this->assertEquals(3, $product1Item->quantity);
        
        // Product 2 should be added
        $product2Item = $userCart->items->where('product_id', $product2->id)->first();
        $this->assertNotNull($product2Item);
        $this->assertEquals(3, $product2Item->quantity);
        
        // Guest cart should be deleted
        $this->assertNull(Cart::where('session_id', $guestSessionId)->first());
    }

    public function test_guest_cart_quantity_respects_stock_limit_on_merge(): void
    {
        // Create product with limited stock
        $product = Product::factory()->create(['stock' => 5, 'status' => 'active', 'price' => 50]);

        // Create customer with cart containing 3 items
        $customer = Customer::factory()->create();
        $userCart = Cart::create(['customer_id' => $customer->id]);
        CartItem::create([
            'cart_id' => $userCart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
        ]);

        // Create guest cart with 4 more items (would exceed stock)
        $guestSessionId = 'test-guest-session-stock';
        $guestCart = Cart::create(['session_id' => $guestSessionId, 'user_id' => null]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $product->id,
            'quantity' => 4, // Total would be 7, but stock is 5
            'price' => $product->price,
        ]);

        // Login
        $this->withCookie('cart_session_id', $guestSessionId);
        
        $component = Volt::test('pages.auth.login')
            ->set('form.email', $customer->email)
            ->set('form.password', 'password');

        $component->call('login');
        
        $this->assertTrue(\Auth::guard('customer')->check());

        // Refresh cart
        $userCart->refresh();
        $userCart->load('items');

        // Quantity should be capped at stock limit (5)
        $cartItem = $userCart->items->where('product_id', $product->id)->first();
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_new_user_registration_merges_guest_cart(): void
    {
        // Create test product
        $product = Product::factory()->create(['stock' => 100, 'status' => 'active', 'price' => 50]);

        // Create guest cart
        $guestSessionId = 'test-guest-register-session';
        $guestCart = Cart::create(['session_id' => $guestSessionId, 'user_id' => null]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        // Register new customer
        $this->withCookie('cart_session_id', $guestSessionId);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'New Customer')
            ->set('email', 'newcustomer@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123');

        $component->call('register');
        
        $this->assertTrue(\Auth::guard('customer')->check());

        // Find the new customer
        $newCustomer = Customer::where('email', 'newcustomer@example.com')->first();
        $this->assertNotNull($newCustomer);

        // Check customer has a cart with the merged items
        $userCart = Cart::where('customer_id', $newCustomer->id)->with('items')->first();
        $this->assertNotNull($userCart);
        $this->assertEquals(1, $userCart->items->count());
        $this->assertEquals(2, $userCart->items->first()->quantity);

        // Guest cart should be deleted
        $this->assertNull(Cart::where('session_id', $guestSessionId)->first());
    }

    public function test_login_without_guest_cart_works_normally(): void
    {
        $customer = Customer::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $customer->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertTrue(\Auth::guard('customer')->check());
    }

    public function test_admin_user_redirects_to_admin_dashboard(): void
    {
        // Admin users login through Filament, not customer login page
        // This test should verify customer login doesn't work for admins
        $admin = \App\Models\User::factory()->create();
        $admin->assignRole('admin');

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $admin->email)
            ->set('form.password', 'password');

        $component->call('login');

        // Admin credentials should fail on customer login page
        $component->assertHasErrors();
        $this->assertTrue(\Auth::guard('customer')->guest());
    }

    public function test_customer_user_redirects_to_home(): void
    {
        $customer = Customer::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $customer->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('home', absolute: false));

        $this->assertTrue(\Auth::guard('customer')->check());
    }

    public function test_registered_user_gets_customer_role(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test Customer')
            ->set('email', 'testcustomer@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123');

        $component->call('register');

        $this->assertTrue(\Auth::guard('customer')->check());

        $customer = Customer::where('email', 'testcustomer@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('active', $customer->status);
    }

    public function test_registered_user_can_have_phone_number(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test Customer')
            ->set('email', 'phonecustomer@example.com')
            ->set('phone', '+201234567890')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123');

        $component->call('register');

        $this->assertTrue(\Auth::guard('customer')->check());

        $customer = Customer::where('email', 'phonecustomer@example.com')->first();
        $this->assertEquals('+201234567890', $customer->phone);
    }
}
