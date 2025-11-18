<?php

// Quick test script for CartService

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CartService;
use App\Models\Product;

try {
    $service = new CartService();
    
    // Get first product
    $product = Product::first();
    
    if (!$product) {
        echo "❌ No products found in database\n";
        exit(1);
    }
    
    echo "✅ Testing with product: {$product->name}\n";
    echo "   Stock: {$product->stock}\n";
    echo "   Price: {$product->price}\n\n";
    
    // Test add to cart
    echo "🛒 Adding to cart...\n";
    $result = $service->addToCart($product->id, 1);
    
    if ($result['success']) {
        echo "✅ SUCCESS: {$result['message']}\n";
        
        // Get cart count
        $count = $service->getCartCount();
        echo "   Cart count: {$count}\n";
        
        // Get cart
        $cart = $service->getCart();
        if ($cart) {
            echo "   Cart ID: {$cart->id}\n";
            echo "   Items in cart: {$cart->items->count()}\n";
        }
    } else {
        echo "❌ FAILED: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
