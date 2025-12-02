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

// Products Listing Page (Task 9.3)
Route::get('/products', [App\Http\Controllers\Store\ProductsController::class, 'index'])->name('products.index');

// Product Details Page (Task 9.4)
Route::get('/products/{slug}', [App\Http\Controllers\Store\ProductDetailsController::class, 'show'])->name('product.show');

// Shopping Cart (Task 9.5)
Route::get('/cart', App\Livewire\Store\CartPage::class)->name('cart');

// Checkout Page (Task 9.7 - Part 1)
Route::get('/checkout', App\Livewire\Store\CheckoutPage::class)->name('checkout');

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
