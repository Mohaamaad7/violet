<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes - Protected by auth and role middleware
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super-admin|admin|manager'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::get('/categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::post('/categories/{id}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::post('/categories/{id}/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
    Route::post('/categories/{id}/move', [CategoryController::class, 'move'])->name('categories.move');
    Route::get('/categories/{id}/stats', [CategoryController::class, 'stats'])->name('categories.stats');
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::get('/products/featured', [ProductController::class, 'featured'])->name('products.featured');
    Route::get('/products/on-sale', [ProductController::class, 'onSale'])->name('products.on-sale');
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::get('/products/out-of-stock', [ProductController::class, 'outOfStock'])->name('products.out-of-stock');
    Route::post('/products/{id}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::post('/products/{id}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::post('/products/{id}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
    Route::post('/products/{id}/update-price', [ProductController::class, 'updatePrice'])->name('products.update-price');
    Route::get('/products/{id}/stats', [ProductController::class, 'stats'])->name('products.stats');
    Route::apiResource('products', ProductController::class);

    // Orders
    Route::get('/orders/stats', [OrderController::class, 'stats'])->name('orders.stats');
    Route::get('/orders/recent', [OrderController::class, 'recent'])->name('orders.recent');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{id}/update-payment', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment');
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::apiResource('orders', OrderController::class)->except(['store', 'update', 'destroy']);
});

// Public API Routes (for testing)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/on-sale', [ProductController::class, 'onSale']);
});
