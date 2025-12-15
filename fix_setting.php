<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the setting
$setting = \App\Models\Setting::where('key', 'logo_icon')->first();

if ($setting) {
    $rawValue = $setting->getAttributes()['value'];
    
    echo "Before Update:\n";
    echo "Raw Value: " . var_export($rawValue, true) . "\n";
    echo "Type: " . gettype($rawValue) . "\n";
    
    // Update if it's false or 'false' string
    if ($rawValue === false || $rawValue === 'false' || $rawValue === 'FALSE') {
        echo "\nUpdating to NULL...\n";
        \DB::table('settings')
            ->where('id', $setting->id)
            ->update(['value' => null]);
        echo "Updated!\n";
        
        // Refetch
        $setting = \App\Models\Setting::where('key', 'logo_icon')->first();
        echo "\nAfter Update:\n";
        echo "Raw Value: " . var_export($setting->getAttributes()['value'], true) . "\n";
    } else {
        echo "\nValue is OK, no update needed.\n";
    }
} else {
    echo "Setting not found\n";
}
