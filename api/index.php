<?php

// Catch ALL errors and display them in the browser response
// This bypasses Vercel's log truncation entirely
set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

try {
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

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "=== RAAHI DEBUG OUTPUT ===\n\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "--- Previous Exception ---\n";
    $prev = $e->getPrevious();
    if ($prev) {
        echo "Exception: " . get_class($prev) . "\n";
        echo "Message: " . $prev->getMessage() . "\n";
        echo "File: " . $prev->getFile() . "\n";
        echo "Line: " . $prev->getLine() . "\n";
    } else {
        echo "(none)\n";
    }
}
