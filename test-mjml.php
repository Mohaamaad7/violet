<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;

$template = EmailTemplate::first();

if (!$template) {
    echo "No templates found!\n";
    exit(1);
}

echo "Testing template: {$template->name} ({$template->slug})\n";
echo "----------------------------------------\n";

$service = new EmailTemplateService();

// Validate MJML
$validation = $service->validateMjml($template->content_mjml);
echo "MJML Valid: " . ($validation['valid'] ? 'Yes' : 'No') . "\n";
echo "Has Errors: " . ($validation['has_errors'] ? 'Yes' : 'No') . "\n";

if (!empty($validation['errors'])) {
    echo "Errors:\n";
    foreach ($validation['errors'] as $error) {
        echo "  - Line {$error['line']}: {$error['message']}\n";
    }
}

// Test rendering
echo "\n----------------------------------------\n";
echo "Testing HTML rendering...\n";

try {
    $html = $service->preview($template, 'ar');
    echo "HTML Generated: " . strlen($html) . " bytes\n";
    echo "Contains DOCTYPE: " . (str_contains($html, '<!doctype html>') ? 'Yes' : 'No') . "\n";
    echo "Contains Arabic text: " . (str_contains($html, 'شكراً') ? 'Yes' : 'No') . "\n";
    echo "\n✅ MJML conversion successful!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
