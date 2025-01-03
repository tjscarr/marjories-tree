<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());

    // Place this before the final response
$memory = memory_get_peak_usage(true) / 1024 / 1024;
$initialMemory = memory_get_usage(true) / 1024 / 1024;
error_log("Peak Memory Usage: {$memory} MB");
error_log("Initial Memory Usage: {$initialMemory} MB");