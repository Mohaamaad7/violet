<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Observer ===\n\n";

// Get order
$order = App\Models\Order::find(3);
echo "Order ID: {$order->id}\n";
echo "Current Status: {$order->status}\n";
echo "Stock Deducted At: " . ($order->stock_deducted_at ?? 'NULL') . "\n\n";

// Get product stock before
$product = $order->items->first()->product;
echo "Product: {$product->name}\n";
echo "Stock BEFORE: {$product->stock}\n";
echo "Order Quantity: {$order->items->first()->quantity}\n\n";

// Change status to shipped
echo "Changing status to 'shipped'...\n";
$order->update(['status' => 'shipped']);

// Refresh and check
$order = $order->fresh();
$product = $product->fresh();

echo "\n=== After Update ===\n";
echo "New Status: {$order->status}\n";
echo "Stock Deducted At: " . ($order->stock_deducted_at ?? 'NULL') . "\n";
echo "Product Stock AFTER: {$product->stock}\n";

// Check logs
echo "\n=== Checking Logs ===\n";
$logContent = file_get_contents(__DIR__.'/storage/logs/laravel.log');
if (strpos($logContent, 'Stock deducted') !== false) {
    echo "✅ Observer logged stock deduction\n";
} else {
    echo "❌ No stock deduction log found\n";
}

echo "\nDone.\n";
