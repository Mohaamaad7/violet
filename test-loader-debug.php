<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Debug translation loader
echo "=== Translation Loader Debug ===" . PHP_EOL;
$translator = app('translator');
echo "Translator class: " . get_class($translator) . PHP_EOL;

$loader = app('translation.loader');
echo "Loader class: " . get_class($loader) . PHP_EOL;
echo PHP_EOL;

// Test loading 'store' group
app()->setLocale('en');
echo "Loading 'store' group for 'en' locale:" . PHP_EOL;
$lines = $loader->load('en', 'store', null);
echo "Loaded " . count($lines) . " keys" . PHP_EOL;
echo "Sample keys: " . PHP_EOL;
$count = 0;
foreach ($lines as $key => $value) {
    echo "  - $key => $value" . PHP_EOL;
    $count++;
    if ($count >= 5) break;
}
echo PHP_EOL;

// Test actual translation
echo "Testing trans('store.header.home'):" . PHP_EOL;
echo "Result: " . trans('store.header.home') . PHP_EOL;
echo PHP_EOL;

// Force reload
app('translator')->load('en', 'store');
echo "After force reload, trans('store.header.home'):" . PHP_EOL;
echo "Result: " . trans('store.header.home') . PHP_EOL;

echo PHP_EOL . "✅ Debug Complete!" . PHP_EOL;
