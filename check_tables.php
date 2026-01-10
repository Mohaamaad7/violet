<?php

// Connect and check tables
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = [
    'customers' => ['wishlists', 'cart_items', 'carts', 'shipping_addresses', 'customers'],
    'orders' => ['order_items', 'orders'],
    'returns' => ['return_items', 'returns'],
    'products' => ['product_images', 'product_reviews', 'product_variants', 'products', 'categories'],
    'inventory' => ['stock_count_items', 'stock_counts', 'stock_movements', 'batches', 'warehouses'],
    'finance' => ['payments', 'commission_payouts', 'influencer_commissions', 'code_usages'],
    'influencers' => ['influencer_applications', 'influencers', 'discount_codes'],
    'content' => ['blog_posts', 'pages', 'banners', 'sliders', 'help_entries'],
    'email_logs' => ['email_logs'],
    'failed_jobs' => ['failed_jobs', 'jobs'],
    'staff' => ['users'],
    'activity_logs' => ['activity_log']
];

echo "=== TABLE VERIFICATION REPORT ===\n\n";

$errors = [];

foreach ($tables as $category => $tableList) {
    echo "--- " . strtoupper($category) . " ---\n";
    foreach ($tableList as $table) {
        $exists = Schema::hasTable($table);
        if ($exists) {
            $count = DB::table($table)->count();
            echo "  ✓ {$table}: {$count} records\n";
        } else {
            echo "  ✗ {$table}: MISSING!\n";
            $errors[] = "{$category}.{$table}";
        }
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "=== ERRORS FOUND ===\n";
    foreach ($errors as $err) {
        echo "  - {$err}\n";
    }
} else {
    echo "=== ALL TABLES VERIFIED OK ===\n";
}
