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

// Forward Vercel requests to the normal Laravel entry point
require __DIR__ . '/../public/index.php';
