<?php

$storageDir = '/tmp/storage';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
    mkdir($storageDir . '/app', 0777, true);
    mkdir($storageDir . '/framework/cache', 0777, true);
    mkdir($storageDir . '/framework/sessions', 0777, true);
    mkdir($storageDir . '/framework/views', 0777, true);
    mkdir($storageDir . '/logs', 0777, true);
}

putenv('VIEW_COMPILED_PATH=' . $storageDir . '/framework/views');
putenv('APP_SERVICES_CACHE=' . '/tmp/services.php');
putenv('APP_PACKAGES_CACHE=' . '/tmp/packages.php');
putenv('APP_CONFIG_CACHE=' . '/tmp/config.php');
putenv('APP_ROUTES_CACHE=' . '/tmp/routes.php');
putenv('APP_EVENTS_CACHE=' . '/tmp/events.php');

// Forward Vercel requests to the normal Laravel entry point
require __DIR__ . '/../public/index.php';
