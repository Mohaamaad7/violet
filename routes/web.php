<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\LanguageController;

// Language switcher
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/locale/{locale}', [LanguageController::class, 'switch'])->name('locale.switch');

// Store Front
Route::get('/', App\Livewire\Store\Home::class)->name('home');

// Cosmetics Theme Landing Page (Task 9.8)
Route::get('/cosmetics', App\Livewire\Cosmetics\HomePage::class)->name('cosmetics.home');

// Products Listing Page (Task 9.3)
Route::get('/products', [App\Http\Controllers\Store\ProductsController::class, 'index'])->name('products.index');

// Product Details Page (Task 9.4)
Route::get('/products/{slug}', [App\Http\Controllers\Store\ProductDetailsController::class, 'show'])->name('product.show');

// Shopping Cart (Task 9.5)
Route::get('/cart', App\Livewire\Store\CartPage::class)->name('cart');

// Wishlist Page (Task 4.3 - Phase 4)
Route::get('/wishlist', App\Livewire\Store\WishlistPage::class)->middleware('auth:customer')->name('wishlist');

// Checkout Page (Task 9.7 - Part 1)
Route::get('/checkout', App\Livewire\Store\CheckoutPage::class)->name('checkout');

// Order Success Page (Task 9.7 - Part 2)
// Security handled in component: users can only view their own orders
Route::get('/checkout/success/{order}', App\Livewire\Store\OrderSuccessPage::class)->name('checkout.success');

// Guest Order Tracking (Task 4.4 - Phase 4)
Route::get('/track-order', App\Livewire\Store\TrackOrder::class)->name('track-order');

// About Us Page (Static Page)
Route::view('/about', 'pages.about')->name('about');

// Contact Us Page (Static Page with Livewire Form)
Route::view('/contact', 'pages.contact')->name('contact');

// Customer Account Area (Task 4.2 - Phase 4)
// Uses customer guard for authentication
Route::middleware(['auth:customer'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', App\Livewire\Store\Account\Dashboard::class)->name('dashboard');
    Route::get('/profile', App\Livewire\Store\Account\Profile::class)->name('profile');
    Route::get('/addresses', App\Livewire\Store\Account\Addresses::class)->name('addresses');
    Route::get('/orders', App\Livewire\Store\Account\Orders::class)->name('orders');
    Route::get('/orders/{order}', App\Livewire\Store\Account\OrderDetails::class)->name('orders.show');
    Route::get('/reviews', App\Livewire\Store\Account\MyReviews::class)->name('reviews');
});

// Legacy route redirect
Route::middleware(['auth:customer'])->get('/orders', fn() => redirect()->route('account.orders'))->name('store.orders.index');

// Debug Route (Temporary - for troubleshooting)
Route::get('/test-cart-debug', function () {
    return view('debug.cart-test');
})->name('debug.cart');

Route::get('/categories/{category:slug}', App\Livewire\Store\CategoryShow::class)->name('category.show');

// Admin Dashboard (for users table - staff/admins)
Route::middleware(['auth'])->group(function () {
    Route::redirect('/dashboard', '/admin')->name('dashboard');
    Route::redirect('/profile', '/admin/profile')->name('profile');
});


// Admin Product Image Management Routes
Route::prefix('admin/products')->middleware(['auth'])->name('admin.products.')->group(function () {
    Route::post('{product}/upload-images', [App\Http\Controllers\Admin\ProductImageController::class, 'upload'])->name('upload-images');
    Route::post('images/{image}/set-primary', [App\Http\Controllers\Admin\ProductImageController::class, 'setPrimary'])->name('images.set-primary');
    Route::delete('images/{image}', [App\Http\Controllers\Admin\ProductImageController::class, 'destroy'])->name('images.destroy');
    Route::post('{product}/images/update-order', [App\Http\Controllers\Admin\ProductImageController::class, 'updateOrder'])->name('images.update-order');
});

// Stock Count Reports (Admin)
Route::prefix('admin/stock-counts')->middleware(['auth'])->name('admin.stock-counts.')->group(function () {
    Route::get('{stockCount}/count-sheet', [App\Http\Controllers\StockCountReportController::class, 'countSheet'])->name('count-sheet');
    Route::get('{stockCount}/results', [App\Http\Controllers\StockCountReportController::class, 'results'])->name('results');
    Route::get('{stockCount}/shortage', [App\Http\Controllers\StockCountReportController::class, 'shortage'])->name('shortage');
    Route::get('{stockCount}/excess', [App\Http\Controllers\StockCountReportController::class, 'excess'])->name('excess');
});

// ========================================
// Payment Routes (Kashier Integration)
// ========================================
Route::prefix('payment')->name('payment.')->group(function () {
    // Select payment method page
    Route::get('/checkout/{order}', [App\Http\Controllers\PaymentController::class, 'selectMethod'])
        ->name('select');

    // Process payment - redirect to Kashier
    Route::match(['get', 'post'], '/process/{order}', [App\Http\Controllers\PaymentController::class, 'process'])
        ->name('process')
        ->middleware('throttle:5,1'); // Rate limit: 5 requests/minute

    // Callback from Kashier (user redirect)
    Route::get('/callback', [App\Http\Controllers\PaymentController::class, 'callback'])
        ->name('callback');

    // Success page
    Route::get('/success/{order}', [App\Http\Controllers\PaymentController::class, 'success'])
        ->name('success');

    // Failed page
    Route::get('/failed/{order}', [App\Http\Controllers\PaymentController::class, 'failed'])
        ->name('failed');
});

// Kashier Webhook (no CSRF, async notification)
Route::post('/webhooks/kashier', [App\Http\Controllers\PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__ . '/auth.php';

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
