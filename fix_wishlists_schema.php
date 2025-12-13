<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Connect to testing database
DB::disconnect();
config(['database.connections.mysql.database' => 'violet_testing']);
DB::reconnect();

echo "Connected to violet_testing database\n";

// Drop user_id from wishlists
try {
    DB::statement('ALTER TABLE wishlists DROP FOREIGN KEY wishlists_user_id_foreign');
    echo "Dropped foreign key wishlists_user_id_foreign\n";
} catch (Exception $e) {
    echo "Foreign key doesn't exist or already dropped\n";
}

try {
    DB::statement('ALTER TABLE wishlists DROP INDEX wishlists_user_id_product_id_unique');
    echo "Dropped index wishlists_user_id_product_id_unique\n";
} catch (Exception $e) {
    echo "Index doesn't exist or already dropped\n";
}

try {
    DB::statement('ALTER TABLE wishlists DROP COLUMN user_id');
    echo "Dropped column user_id\n";
} catch (Exception $e) {
    echo "Column doesn't exist or already dropped\n";
}

echo "\nChecking schema:\n";
$columns = DB::select('DESCRIBE wishlists');
foreach ($columns as $col) {
    echo "  - {$col->Field} ({$col->Type})\n";
}
