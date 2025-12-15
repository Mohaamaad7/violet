<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$setting = \App\Models\Setting::where('key', 'logo_icon')->first();

if ($setting) {
    echo "Setting found:\n";
    echo "ID: " . $setting->id . "\n";
    echo "Key: " . $setting->key . "\n";
    echo "Type: " . $setting->type . "\n";
    echo "Raw Value (from attributes): " . var_export($setting->getAttributes()['value'], true) . "\n";
    echo "Accessor Value: " . var_export($setting->value, true) . "\n";
    echo "Value Type: " . gettype($setting->value) . "\n";
} else {
    echo "Setting not found\n";
}
