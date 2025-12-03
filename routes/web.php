<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\LanguageController;

// Language switcher
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/locale/{locale}', [LanguageController::class, 'switch'])->name('locale.switch');

// Store Front
Route::get('/', [App\Http\Controllers\Store\HomeController::class, 'index'])->name('home');

// Cosmetics Theme Landing Page (Task 9.8)
Route::get('/cosmetics', App\Livewire\Cosmetics\HomePage::class)->name('cosmetics.home');

// Products Listing Page (Task 9.3)
Route::get('/products', [App\Http\Controllers\Store\ProductsController::class, 'index'])->name('products.index');

// Product Details Page (Task 9.4)
Route::get('/products/{slug}', [App\Http\Controllers\Store\ProductDetailsController::class, 'show'])->name('product.show');

// Shopping Cart (Task 9.5)
Route::get('/cart', App\Livewire\Store\CartPage::class)->name('cart');

// Wishlist Page (Task 4.3 - Phase 4)
Route::get('/wishlist', App\Livewire\Store\WishlistPage::class)->middleware('auth')->name('wishlist');

// Checkout Page (Task 9.7 - Part 1)
Route::get('/checkout', App\Livewire\Store\CheckoutPage::class)->name('checkout');

// Order Success Page (Task 9.7 - Part 2)
// Security handled in component: users can only view their own orders
Route::get('/checkout/success/{order}', App\Livewire\Store\OrderSuccessPage::class)->name('checkout.success');

// Guest Order Tracking (Task 4.4 - Phase 4)
Route::get('/track-order', App\Livewire\Store\TrackOrder::class)->name('track-order');

// Customer Account Area (Task 4.2 - Phase 4)
Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', App\Livewire\Store\Account\Dashboard::class)->name('dashboard');
    Route::get('/profile', App\Livewire\Store\Account\Profile::class)->name('profile');
    Route::get('/addresses', App\Livewire\Store\Account\Addresses::class)->name('addresses');
    Route::get('/orders', App\Livewire\Store\Account\Orders::class)->name('orders');
    Route::get('/orders/{order}', App\Livewire\Store\Account\OrderDetails::class)->name('orders.show');
    Route::get('/reviews', App\Livewire\Store\Account\MyReviews::class)->name('reviews');
});

// Legacy route redirect
Route::middleware(['auth'])->get('/orders', fn() => redirect()->route('account.orders'))->name('store.orders.index');

// Debug Route (Temporary - for troubleshooting)
Route::get('/test-cart-debug', function () {
    return view('debug.cart-test');
})->name('debug.cart');

Route::get('/categories/{category:slug}', function () {
    return 'Category page (Coming soon)';
})->name('category.show');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Admin Product Image Management Routes
Route::prefix('admin/products')->middleware(['auth'])->name('admin.products.')->group(function () {
    Route::post('{product}/upload-images', [App\Http\Controllers\Admin\ProductImageController::class, 'upload'])->name('upload-images');
    Route::post('images/{image}/set-primary', [App\Http\Controllers\Admin\ProductImageController::class, 'setPrimary'])->name('images.set-primary');
    Route::delete('images/{image}', [App\Http\Controllers\Admin\ProductImageController::class, 'destroy'])->name('images.destroy');
    Route::post('{product}/images/update-order', [App\Http\Controllers\Admin\ProductImageController::class, 'updateOrder'])->name('images.update-order');
});

require __DIR__.'/auth.php';

// Public API Routes
Route::prefix('api')->name('api.')->group(function () {
    // Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{id}', [CategoryController::class, 'show'])->name('categories.show');
    
    // Products
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/featured', [ProductController::class, 'featured'])->name('products.featured');
    Route::get('products/on-sale', [ProductController::class, 'onSale'])->name('products.on-sale');
    Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');
});
