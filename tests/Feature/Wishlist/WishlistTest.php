<?php

namespace Tests\Feature\Wishlist;

use App\Livewire\Store\WishlistButton;
use App\Livewire\Store\WishlistPage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customer = Customer::factory()->create([
            'status' => 'active',
        ]);
        
        $this->product = Product::factory()->create([
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function guest_cannot_access_wishlist_page(): void
    {
        $this->get(route('wishlist'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_wishlist_page(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get(route('wishlist'))
            ->assertOk()
            ->assertSeeLivewire(WishlistPage::class);
    }

    /** @test */
    public function wishlist_page_shows_empty_state_when_no_items(): void
    {
        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistPage::class)
            ->assertSee(__('messages.wishlist.empty'));
    }

    /** @test */
    public function wishlist_service_can_add_product(): void
    {
        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        $wishlistItem = $service->add($this->product->id);
        
        $this->assertDatabaseHas('wishlists', [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function wishlist_service_can_remove_product(): void
    {
        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        // First add
        $service->add($this->product->id);
        
        // Then remove
        $removed = $service->remove($this->product->id);
        
        $this->assertTrue($removed);
        $this->assertDatabaseMissing('wishlists', [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function wishlist_service_can_toggle_product(): void
    {
        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        // First toggle - should add
        $result1 = $service->toggle($this->product->id);
        $this->assertEquals('added', $result1['action']);
        $this->assertTrue($result1['in_wishlist']);
        
        // Second toggle - should remove
        $result2 = $service->toggle($this->product->id);
        $this->assertEquals('removed', $result2['action']);
        $this->assertFalse($result2['in_wishlist']);
    }

    /** @test */
    public function wishlist_button_toggles_state(): void
    {
        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistButton::class, ['productId' => $this->product->id])
            ->assertSet('inWishlist', false)
            ->call('toggle')
            ->assertSet('inWishlist', true)
            ->assertDispatched('wishlist-updated')
            ->call('toggle')
            ->assertSet('inWishlist', false);
    }

    /** @test */
    public function guest_cannot_toggle_wishlist_button(): void
    {
        Livewire::test(WishlistButton::class, ['productId' => $this->product->id])
            ->call('toggle')
            ->assertDispatched('show-toast');
    }

    /** @test */
    public function wishlist_page_shows_saved_products(): void
    {
        // Add product to wishlist
        Wishlist::factory()->create([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistPage::class)
            ->assertSee($this->product->name);
    }

    /** @test */
    public function can_remove_product_from_wishlist_page(): void
    {
        Wishlist::factory()->create([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistPage::class)
            ->call('removeFromWishlist', $this->product->id)
            ->assertDispatched('wishlist-updated')
            ->assertDispatched('show-toast');

        $this->assertDatabaseMissing('wishlists', [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function can_move_product_to_cart_from_wishlist(): void
    {
        Wishlist::factory()->create([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistPage::class)
            ->call('moveToCart', $this->product->id)
            ->assertDispatched('cart-updated')
            ->assertDispatched('wishlist-updated');

        // Verify removed from wishlist
        $this->assertDatabaseMissing('wishlists', [
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function can_clear_entire_wishlist(): void
    {
        // Add multiple products
        Wishlist::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);

        Livewire::actingAs($this->customer, 'customer')
            ->test(WishlistPage::class)
            ->call('clearWishlist')
            ->assertDispatched('wishlist-updated');

        $this->assertEquals(0, Wishlist::where('customer_id', $this->customer->id)->count());
    }

    /** @test */
    public function wishlist_count_returns_correct_number(): void
    {
        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        // Start with 0
        $this->assertEquals(0, $service->getWishlistCount());
        
        // Add products
        Wishlist::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);
        
        $this->assertEquals(3, $service->getWishlistCount());
    }

    /** @test */
    public function users_can_only_see_their_own_wishlist(): void
    {
        $otherCustomer = Customer::factory()->create();
        
        // Create wishlist item for other customer
        Wishlist::factory()->create([
            'customer_id' => $otherCustomer->id,
            'product_id' => $this->product->id,
        ]);

        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        // Should not see other customer's wishlist
        $this->assertFalse($service->isInWishlist($this->product->id));
    }

    /** @test */
    public function duplicate_wishlist_items_are_prevented(): void
    {
        $service = app(WishlistService::class);
        
        $this->actingAs($this->customer, 'customer');
        
        // Add twice
        $service->add($this->product->id);
        $service->add($this->product->id);
        
        // Should only have one entry
        $this->assertEquals(1, Wishlist::where('customer_id', $this->customer->id)
            ->where('product_id', $this->product->id)
            ->count());
    }
}
