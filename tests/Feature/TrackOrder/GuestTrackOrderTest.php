<?php

namespace Tests\Feature\TrackOrder;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestTrackOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function track_order_page_renders_successfully(): void
    {
        $response = $this->get(route('track-order'));
        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\Store\TrackOrder::class);
    }

    /** @test */
    public function guest_can_track_order_with_email(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-TEST-001',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '01234567890',
            'guest_name' => 'Guest User',
            'guest_city' => 'Cairo',
            'guest_governorate' => 'Cairo',
            'guest_address' => '123 Test Street',
            'user_id' => null,
            'status' => 'processing',
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-TEST-001')
            ->set('contactInfo', 'guest@example.com')
            ->call('track')
            ->assertSet('order.id', $order->id)
            ->assertSet('errorMessage', '');
    }

    /** @test */
    public function guest_can_track_order_with_phone(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-TEST-002',
            'guest_email' => 'guest2@example.com',
            'guest_phone' => '01098765432',
            'user_id' => null,
            'status' => 'shipped',
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-TEST-002')
            ->set('contactInfo', '01098765432')
            ->call('track')
            ->assertSet('order.id', $order->id);
    }

    /** @test */
    public function shows_error_when_order_not_found(): void
    {
        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'NONEXISTENT')
            ->set('contactInfo', 'wrong@email.com')
            ->call('track')
            ->assertSet('order', null)
            ->assertSet('searched', true)
            ->assertNotSet('errorMessage', '');
    }

    /** @test */
    public function requires_both_order_number_and_contact_info(): void
    {
        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', '')
            ->set('contactInfo', '')
            ->call('track')
            ->assertHasErrors(['orderNumber', 'contactInfo']);
    }

    /** @test */
    public function registered_user_can_track_order_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'phone' => '01111111111',
        ]);

        $order = Order::factory()->create([
            'order_number' => 'ORD-USER-001',
            'user_id' => $user->id,
            'guest_email' => null,
            'guest_phone' => null,
            'status' => 'delivered',
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-USER-001')
            ->set('contactInfo', 'user@example.com')
            ->call('track')
            ->assertSet('order.id', $order->id);
    }

    /** @test */
    public function clear_resets_the_form(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-CLEAR-001',
            'guest_email' => 'clear@example.com',
            'user_id' => null,
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-CLEAR-001')
            ->set('contactInfo', 'clear@example.com')
            ->call('track')
            ->assertSet('order.id', $order->id)
            ->call('clear')
            ->assertSet('orderNumber', '')
            ->assertSet('contactInfo', '')
            ->assertSet('order', null)
            ->assertSet('searched', false);
    }

    /** @test */
    public function displays_order_items(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-ITEMS-001',
            'guest_email' => 'items@example.com',
            'user_id' => null,
        ]);

        $product = Product::factory()->create(['name' => 'Test Product']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 100,
            'subtotal' => 200,
        ]);

        $component = Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-ITEMS-001')
            ->set('contactInfo', 'items@example.com')
            ->call('track');
        
        $this->assertCount(1, $component->get('order.items'));
    }

    /** @test */
    public function displays_status_timeline(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-TIMELINE-001',
            'guest_email' => 'timeline@example.com',
            'user_id' => null,
            'status' => 'processing',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'notes' => 'Order placed',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'processing',
            'notes' => 'Order being processed',
        ]);

        $component = Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-TIMELINE-001')
            ->set('contactInfo', 'timeline@example.com')
            ->call('track');

        $timeline = $component->viewData('this')->statusTimeline;
        
        $this->assertNotEmpty($timeline);
        $this->assertCount(5, $timeline); // pending, confirmed, processing, shipped, delivered
    }

    /** @test */
    public function wrong_email_for_order_shows_error(): void
    {
        Order::factory()->create([
            'order_number' => 'ORD-SECURE-001',
            'guest_email' => 'correct@example.com',
            'guest_phone' => '01234567890',
            'user_id' => null,
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-SECURE-001')
            ->set('contactInfo', 'wrong@example.com')
            ->call('track')
            ->assertSet('order', null)
            ->assertNotSet('errorMessage', '');
    }

    /** @test */
    public function cancelled_order_shows_cancelled_status(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-CANCEL-001',
            'guest_email' => 'cancel@example.com',
            'user_id' => null,
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Customer request',
        ]);

        $component = Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', 'ORD-CANCEL-001')
            ->set('contactInfo', 'cancel@example.com')
            ->call('track');

        $this->assertEquals('cancelled', $component->get('order.status'));
    }

    /** @test */
    public function validation_trims_whitespace(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-TRIM-001',
            'guest_email' => 'trim@example.com',
            'user_id' => null,
        ]);

        Livewire::test(\App\Livewire\Store\TrackOrder::class)
            ->set('orderNumber', '  ORD-TRIM-001  ')
            ->set('contactInfo', '  trim@example.com  ')
            ->call('track')
            ->assertSet('order.id', $order->id);
    }
}
