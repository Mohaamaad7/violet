<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CRITICAL BUG INVESTIGATION ===\n\n";

// Find product
$product = App\Models\Product::find(27);
echo "Product ID: 27\n";
echo "Product Name: {$product->name}\n";
echo "Current Stock: {$product->stock}\n\n";

// Get last 10 stock movements
echo "=== Last 10 Stock Movements ===\n";
$movements = App\Models\StockMovement::where('product_id', 27)
    ->latest()
    ->take(10)
    ->get();

foreach ($movements as $m) {
    echo sprintf(
        "%s | Type: %s | Qty: %d | Notes: %s | At: %s\n",
        $m->id,
        $m->type,
        $m->quantity,
        $m->notes ?? 'N/A',
        $m->created_at
    );
}

echo "\n=== Total Deductions ===\n";
$totalOut = App\Models\StockMovement::where('product_id', 27)
    ->where('quantity', '<', 0)
    ->sum('quantity');
echo "Total OUT: {$totalOut}\n";

$totalIn = App\Models\StockMovement::where('product_id', 27)
    ->where('quantity', '>', 0)
    ->sum('quantity');
echo "Total IN: {$totalIn}\n";

echo "\nExpected Stock: " . ($totalIn + $totalOut) . "\n";
echo "Actual Stock: {$product->stock}\n";
