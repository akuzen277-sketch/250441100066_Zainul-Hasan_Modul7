<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$isVercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || (PHP_OS_FAMILY === 'Linux' && is_dir('/tmp'));

if ($isVercel) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;

    $storagePath = '/tmp/storage';
    $dirs = [
        $storagePath . '/app/public',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/views',
        $storagePath . '/logs',
        '/tmp/cache',
        '/tmp/views',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    putenv('APP_CONFIG_CACHE=/tmp/cache/config.php');
    $_ENV['APP_CONFIG_CACHE'] = '/tmp/cache/config.php';

    putenv('APP_EVENTS_CACHE=/tmp/cache/events.php');
    $_ENV['APP_EVENTS_CACHE'] = '/tmp/cache/events.php';

    putenv('APP_PACKAGES_CACHE=/tmp/cache/packages.php');
    $_ENV['APP_PACKAGES_CACHE'] = '/tmp/cache/packages.php';

    putenv('APP_ROUTES_CACHE=/tmp/cache/routes.php');
    $_ENV['APP_ROUTES_CACHE'] = '/tmp/cache/routes.php';

    putenv('APP_SERVICES_CACHE=/tmp/cache/services.php');
    $_ENV['APP_SERVICES_CACHE'] = '/tmp/cache/services.php';

    putenv('VIEW_COMPILED_PATH=/tmp/views');
    $_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
    $_SERVER['VIEW_COMPILED_PATH'] = '/tmp/views';

    if (!getenv('LOG_CHANNEL') || getenv('LOG_CHANNEL') === 'stack') {
        putenv('LOG_CHANNEL=stderr');
        $_ENV['LOG_CHANNEL'] = 'stderr';
    }
}

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

if ($isVercel) {
    $app->useStoragePath($storagePath);
}

$app->handleRequest(Request::capture());
