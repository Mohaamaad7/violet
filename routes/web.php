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

// Influencer Application Page
Route::get('/influencer/apply', App\Livewire\InfluencerApplicationForm::class)->name('influencer.apply');

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

// ========================================
// Customer Password Reset Routes
// ========================================
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\CustomerPasswordResetController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [App\Http\Controllers\Auth\CustomerPasswordResetController::class, 'reset'])
    ->name('password.update');

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
// Payment Routes (Multi-Gateway Support)
// ========================================
Route::prefix('payment')->name('payment.')->group(function () {
    // Select payment method page
    Route::get('/checkout/{order}', [App\Http\Controllers\PaymentController::class, 'selectMethod'])
        ->name('select');

    // Process payment - redirect to active gateway
    Route::match(['get', 'post'], '/process/{order}', [App\Http\Controllers\PaymentController::class, 'process'])
        ->name('process')
        ->middleware('throttle:5,1');

    // ============ Kashier Callbacks ============
    Route::prefix('kashier')->name('kashier.')->group(function () {
        Route::get('/callback', [App\Http\Controllers\PaymentController::class, 'kashierCallback'])
            ->name('callback');
        Route::post('/webhook', [App\Http\Controllers\PaymentController::class, 'kashierWebhook'])
            ->name('webhook')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    });

    // ============ Paymob Callbacks ============
    Route::prefix('paymob')->name('paymob.')->group(function () {
        // Callback can be GET or POST depending on Paymob configuration
        Route::match(['get', 'post'], '/callback', [App\Http\Controllers\PaymentController::class, 'paymobCallback'])
            ->name('callback')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        // Webhook accepts both GET and POST - Paymob wallet sometimes redirects to webhook URL
        Route::match(['get', 'post'], '/webhook', [App\Http\Controllers\PaymentController::class, 'paymobWebhook'])
            ->name('webhook')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    });

    // ============ Legacy Callbacks (Backwards Compatibility) ============
    // @deprecated - Use gateway-specific callbacks instead
    Route::get('/callback', [App\Http\Controllers\PaymentController::class, 'callback'])
        ->name('callback');

    // Success page
    Route::get('/success/{order}', [App\Http\Controllers\PaymentController::class, 'success'])
        ->name('success');

    // Failed page
    Route::get('/failed/{order}', [App\Http\Controllers\PaymentController::class, 'failed'])
        ->name('failed');
});

// Legacy Webhook Route (backwards compatibility)
// @deprecated - Use gateway-specific webhooks instead
Route::post('/webhooks/kashier', [App\Http\Controllers\PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ========================================
// Temporary Diagnostic Routes (DELETE AFTER TESTING)
// ========================================
Route::get('/test-paymob-callback', function () {
    \Illuminate\Support\Facades\Log::info('TEST: Paymob callback route is accessible', [
        'timestamp' => now(),
        'ip' => request()->ip(),
        'query' => request()->query(),
    ]);

    return response()->json([
        'status' => 'ok',
        'message' => 'Paymob callback route is working',
        'timestamp' => now(),
        'received_params' => request()->query(),
    ]);
})->name('test.paymob.callback');

Route::match(['get', 'post'], '/test-paymob-full', function () {
    \Illuminate\Support\Facades\Log::info('TEST: Paymob full callback test', [
        'method' => request()->method(),
        'query' => request()->query(),
        'post' => request()->post(),
        'all' => request()->all(),
        'ip' => request()->ip(),
        'timestamp' => now(),
    ]);

    return response()->json([
        'status' => 'ok',
        'message' => 'Full callback test successful',
        'method' => request()->method(),
        'received_data' => request()->all(),
        'timestamp' => now(),
    ]);
})->name('test.paymob.full');

require __DIR__ . '/auth.php';

// Partners Panel - Password Update API
Route::post('/partners/profile/update-password', function() {
    $user = Auth::user();
    
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
    }
    
    $data = request()->all();
    
    // Validate current password
    if (!Hash::check($data['current_password'] ?? '', $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'كلمة المرور الحالية غير صحيحة'
        ]);
    }
    
    // Validate new password
    if (strlen($data['new_password'] ?? '') < 8) {
        return response()->json([
            'success' => false,
            'message' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل'
        ]);
    }
    
    // Validate confirmation
    if (($data['new_password'] ?? '') !== ($data['new_password_confirmation'] ?? '')) {
        return response()->json([
            'success' => false,
            'message' => 'كلمة المرور الجديدة وتأكيدها غير متطابقين'
        ]);
    }
    
    // Update password
    $user->update([
        'password' => Hash::make($data['new_password'])
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'تم تحديث كلمة المرور بنجاح. سيتم تسجيل خروجك الآن...'
    ]);
})->middleware('auth')->name('partners.profile.update-password');

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
