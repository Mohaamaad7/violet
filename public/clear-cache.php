<?php
/**
 * Emergency Cache Clear Script
 * Access via: https://test.flowerviolet.com/clear-cache.php
 * DELETE THIS FILE AFTER USE!
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Clearing Caches...</h1>";
echo "<pre>";

// Clear view cache
echo "\n[1/6] Clearing view cache...\n";
$kernel->call('view:clear');
echo "✓ View cache cleared\n";

// Clear config cache
echo "\n[2/6] Clearing config cache...\n";
$kernel->call('config:clear');
echo "✓ Config cache cleared\n";

// Clear route cache
echo "\n[3/6] Clearing route cache...\n";
$kernel->call('route:clear');
echo "✓ Route cache cleared\n";

// Clear application cache
echo "\n[4/6] Clearing application cache...\n";
$kernel->call('cache:clear');
echo "✓ Application cache cleared\n";

// Clear Filament cache
echo "\n[5/6] Clearing Filament cache...\n";
try {
    $kernel->call('filament:clear-cached-components');
    echo "✓ Filament cache cleared\n";
} catch (Exception $e) {
    echo "⚠ Filament cache: " . $e->getMessage() . "\n";
}

// Optimize clear (all caches)
echo "\n[6/6] Running optimize:clear...\n";
$kernel->call('optimize:clear');
echo "✓ All optimizations cleared\n";

echo "\n\n=====================================\n";
echo "✅ ALL CACHES CLEARED SUCCESSFULLY!\n";
echo "=====================================\n\n";
echo "🔒 IMPORTANT: Delete this file now for security!\n";
echo "File location: public/clear-cache.php\n";

echo "</pre>";
