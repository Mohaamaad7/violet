<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test TranslationService
$service = app(App\Services\TranslationService::class);

echo "=== Translation Service Test ===" . PHP_EOL;
echo "EN (store.header.home): " . $service->get('store.header.home', 'en') . PHP_EOL;
echo "AR (store.header.home): " . $service->get('store.header.home', 'ar') . PHP_EOL;
echo PHP_EOL;

echo "EN (store.footer.copyright): " . $service->get('store.footer.copyright', 'en') . PHP_EOL;
echo "AR (store.footer.copyright): " . $service->get('store.footer.copyright', 'ar') . PHP_EOL;
echo PHP_EOL;

echo "EN (store.cart.shopping_cart): " . $service->get('store.cart.shopping_cart', 'en') . PHP_EOL;
echo "AR (store.cart.shopping_cart): " . $service->get('store.cart.shopping_cart', 'ar') . PHP_EOL;
echo PHP_EOL;

// Test with Laravel trans() helper
app()->setLocale('en');
echo "=== Laravel trans() Helper (EN) ===" . PHP_EOL;
echo "trans('store.header.home'): " . trans('store.header.home') . PHP_EOL;
echo "__('store.header.products'): " . __('store.header.products') . PHP_EOL;
echo PHP_EOL;

app()->setLocale('ar');
echo "=== Laravel trans() Helper (AR) ===" . PHP_EOL;
echo "trans('store.header.home'): " . trans('store.header.home') . PHP_EOL;
echo "__('store.header.products'): " . __('store.header.products') . PHP_EOL;

echo PHP_EOL . "✅ Test Complete!" . PHP_EOL;
