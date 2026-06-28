<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$paths = app('translator')->getLoader()->namespaces();
echo "Namespaces: " . json_encode($paths) . "\n";
$loader = app('translator')->getLoader();
$reflection = new ReflectionClass($loader);
if ($reflection->hasProperty('fileLoader')) {
    $prop = $reflection->getProperty('fileLoader');
    $prop->setAccessible(true);
    $fileLoader = $prop->getValue($loader);
    $ref2 = new ReflectionClass($fileLoader);
    $pathProp = $ref2->getProperty('path');
    $pathProp->setAccessible(true);
    echo "Default Path: " . $pathProp->getValue($fileLoader) . "\n";
}
echo "Fallback: " . config('app.fallback_locale') . "\n";
