<?php
/**
 * Upload Configuration Diagnostic Script
 * Run this file to check all upload-related settings
 */

echo "=== PHP Upload Configuration ===\n";
echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n\n";

echo "=== Directory Permissions ===\n";
$dirs = [
    'PHP upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'Livewire tmp (storage/app/livewire-tmp)' => __DIR__ . '/storage/app/livewire-tmp',
    'Public storage (storage/app/public)' => __DIR__ . '/storage/app/public',
    'Sliders directory' => __DIR__ . '/storage/app/public/sliders',
    'Banners directory' => __DIR__ . '/storage/app/public/banners',
];

foreach ($dirs as $name => $path) {
    if (empty($path)) {
        echo "❌ $name: NOT SET\n";
        continue;
    }
    
    if (file_exists($path)) {
        echo "✅ $name exists: $path\n";
        echo "   Writable: " . (is_writable($path) ? 'YES' : 'NO') . "\n";
        echo "   Readable: " . (is_readable($path) ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ $name DOES NOT EXIST: $path\n";
    }
}

echo "\n=== Storage Link ===\n";
$storageLinkPath = __DIR__ . '/public/storage';
if (file_exists($storageLinkPath)) {
    echo "✅ public/storage exists\n";
    if (is_link($storageLinkPath)) {
        echo "   Type: Symbolic Link\n";
        echo "   Target: " . readlink($storageLinkPath) . "\n";
    } else {
        echo "   Type: Directory (not a symlink)\n";
    }
} else {
    echo "❌ public/storage DOES NOT EXIST\n";
}

echo "\n=== Test Write Operations ===\n";
$testFile = __DIR__ . '/storage/app/public/sliders/test-write.txt';
$testDir = dirname($testFile);

if (!file_exists($testDir)) {
    mkdir($testDir, 0755, true);
    echo "✅ Created sliders directory\n";
}

if (file_put_contents($testFile, 'test')) {
    echo "✅ Can write to sliders directory\n";
    unlink($testFile);
} else {
    echo "❌ Cannot write to sliders directory\n";
}

echo "\n=== APP_URL Configuration ===\n";
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (preg_match('/APP_URL=(.+)/', $envContent, $matches)) {
        echo "APP_URL: " . trim($matches[1]) . "\n";
    } else {
        echo "❌ APP_URL not found in .env\n";
    }
} else {
    echo "❌ .env file not found\n";
}
