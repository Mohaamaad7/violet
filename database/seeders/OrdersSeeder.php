<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users (we have 3 admin users)
        $users = User::all();
        
        // Get some products
        $products = Product::with('images')->take(20)->get();
        
        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please seed them first.');
            return;
        }

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid', 'failed', 'refunded'];
        $paymentMethods = ['cod', 'card', 'instapay'];

        DB::transaction(function () use ($users, $products, $statuses, $paymentStatuses, $paymentMethods) {
            foreach (range(1, 30) as $index) {
                $user = $users->random();

                $status = fake()->randomElement($statuses);
                $paymentStatus = fake()->randomElement($paymentStatuses);
                
                // Calculate order totals
                $itemCount = fake()->numberBetween(1, 5);
                $subtotal = 0;
                $orderItems = [];

                for ($i = 0; $i < $itemCount; $i++) {
                    $product = $products->random();
                    $quantity = fake()->numberBetween(1, 3);
                    $price = $product->sale_price ?? $product->price;
                    $itemSubtotal = $price * $quantity;
                    
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                    ];
                    
                    $subtotal += $itemSubtotal;
                }

                $shippingCost = fake()->randomFloat(2, 0, 50);
                $taxAmount = $subtotal * 0.14; // 14% tax
                $discountAmount = fake()->boolean(30) ? fake()->randomFloat(2, 0, $subtotal * 0.2) : 0;
                $total = $subtotal + $shippingCost + $taxAmount - $discountAmount;

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-' . str_pad($index, 6, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'payment_method' => fake()->randomElement($paymentMethods),
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'shipping_cost' => $shippingCost,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                    'admin_notes' => fake()->boolean(20) ? fake()->sentence() : null,
                    'payment_transaction_id' => $paymentStatus === 'paid' ? 'TXN-' . fake()->uuid() : null,
                    'paid_at' => $paymentStatus === 'paid' ? fake()->dateTimeBetween('-30 days', '-1 day') : null,
                    'shipped_at' => in_array($status, ['shipped', 'delivered']) ? fake()->dateTimeBetween('-20 days', '-5 days') : null,
                    'delivered_at' => $status === 'delivered' ? fake()->dateTimeBetween('-15 days', 'now') : null,
                    'cancelled_at' => $status === 'cancelled' ? fake()->dateTimeBetween('-10 days', 'now') : null,
                    'cancellation_reason' => $status === 'cancelled' ? fake()->sentence() : null,
                    'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
                ]);

                // Create shipping address for this order (one-to-one relationship)
                ShippingAddress::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'full_name' => fake()->name(),
                    'phone' => fake()->phoneNumber(),
                    'email' => $user->email,
                    'governorate' => fake()->randomElement(['Cairo', 'Giza', 'Alexandria', 'Aswan', 'Luxor']),
                    'city' => fake()->city(),
                    'area' => fake()->streetName(),
                    'street_address' => fake()->streetAddress(),
                    'landmark' => fake()->boolean(50) ? fake()->sentence(3) : null,
                    'postal_code' => fake()->postcode(),
                    'is_default' => false,
                ]);

                // Create order items
                foreach ($orderItems as $itemData) {
                    $order->items()->create($itemData);
                }
            }
        });

        $this->command->info('✅ Created 30 demo orders with items and shipping addresses.');
    }
}
