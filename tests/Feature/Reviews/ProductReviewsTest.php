<?php

namespace Tests\Feature\Reviews;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function user_cannot_review_product_without_delivered_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        $this->assertFalse($service->canReview($product->id));
    }

    /** @test */
    public function user_can_review_product_from_delivered_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        $this->assertTrue($service->canReview($product->id));
    }

    /** @test */
    public function user_cannot_review_same_product_twice(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        // Create delivered order with product
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        
        // Create existing review
        ProductReview::factory()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        $this->assertTrue($service->hasReviewed($product->id));
    }

    /** @test */
    public function review_service_creates_review_with_correct_data(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        
        $review = $service->create([
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product!',
            'comment' => 'Highly recommend this product.',
        ]);
        
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'rating' => 5,
            'title' => 'Great product!',
            'is_verified_purchase' => true,
            'is_approved' => false, // Requires moderation
        ]);
    }

    /** @test */
    public function owner_can_update_own_review(): void
    {
        $customer = Customer::factory()->create();
        
        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'rating' => 3,
            'title' => 'Original title',
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        
        $updated = $service->update($review, [
            'rating' => 5,
            'title' => 'Updated title',
            'comment' => 'Updated comment',
        ]);
        
        $this->assertEquals(5, $updated->rating);
        $this->assertEquals('Updated title', $updated->title);
        $this->assertFalse($updated->is_approved); // Re-moderation
    }

    /** @test */
    public function owner_can_delete_own_review(): void
    {
        $customer = Customer::factory()->create();
        
        $review = ProductReview::factory()->create([
            'customer_id' => $customer->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        
        $result = $service->delete($review);
        
        $this->assertTrue($result);
        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }

    /** @test */
    public function user_cannot_update_others_review(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        
        $review = ProductReview::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        
        $this->expectException(\Exception::class);
        $service->update($review, ['rating' => 5]);
    }

    /** @test */
    public function user_cannot_delete_others_review(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        
        $review = ProductReview::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);
        
        $service = app(ReviewService::class);
        
        $this->actingAs($customer, 'customer');
        
        $this->expectException(\Exception::class);
        $service->delete($review);
    }

    /** @test */
    public function product_reviews_component_renders_for_guest(): void
    {
        $product = Product::factory()->create();
        
        Livewire::test(\App\Livewire\Store\ProductReviews::class, ['product' => $product])
            ->assertOk()
            ->assertSee(__('messages.reviews.login_to_review'));
    }

    /** @test */
    public function product_reviews_component_shows_write_review_for_eligible_user(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        
        // The component shows "tap_to_rate" for eligible users who haven't reviewed yet
        Livewire::actingAs($customer, 'customer')
            ->test(\App\Livewire\Store\ProductReviews::class, ['product' => $product])
            ->assertSee(__('messages.reviews.tap_to_rate'));
    }

    /** @test */
    public function product_reviews_component_can_submit_review(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
        
        Livewire::actingAs($customer, 'customer')
            ->test(\App\Livewire\Store\ProductReviews::class, ['product' => $product])
            ->call('openForm')
            ->set('rating', 4)
            ->set('title', 'Nice product')
            ->set('comment', 'Really enjoyed this product!')
            ->call('submit');
        
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'rating' => 4,
            'title' => 'Nice product',
        ]);
    }

    /** @test */
    public function my_reviews_page_renders_for_authenticated_user(): void
    {
        $customer = Customer::factory()->create();
        
        $this->actingAs($customer, 'customer');
        
        $response = $this->get(route('account.reviews'));
        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\Store\Account\MyReviews::class);
    }

    /** @test */
    public function my_reviews_page_shows_user_reviews(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product Name']);
        
        ProductReview::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'title' => 'My Review Title',
        ]);
        
        Livewire::actingAs($customer, 'customer')
            ->test(\App\Livewire\Store\Account\MyReviews::class)
            ->assertSee('Test Product Name')
            ->assertSee('My Review Title');
    }

    /** @test */
    public function review_stats_are_calculated_correctly(): void
    {
        $product = Product::factory()->create();
        
        // Create 5 approved reviews with different ratings
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 5]);
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 5]);
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 4]);
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 3]);
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 2]);
        
        // Create unapproved review (should not count)
        ProductReview::factory()->pending()->create(['product_id' => $product->id, 'rating' => 1]);
        
        $service = app(ReviewService::class);
        $stats = $service->getProductStats($product->id);
        
        $this->assertEquals(5, $stats['total_count']);
        $this->assertEquals(3.8, $stats['average_rating']); // (5+5+4+3+2)/5 = 3.8
        $this->assertEquals(2, $stats['distribution'][5]['count']);
        $this->assertEquals(1, $stats['distribution'][4]['count']);
    }

    /** @test */
    public function helpful_count_increments(): void
    {
        $review = ProductReview::factory()->approved()->create([
            'helpful_count' => 0,
        ]);
        
        $service = app(ReviewService::class);
        $service->markHelpful($review);
        
        $this->assertEquals(1, $review->fresh()->helpful_count);
    }
}
