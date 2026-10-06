<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

// Serverless handler for Laravel on Vercel
// Ensure writable directories exist in /tmp
$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Mark VERCEL environment
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Ensure compiled Blade views and caches are written to /tmp
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
$_ENV['APP_CONFIG_CACHE'] = '/tmp/bootstrap/cache/config.php';
$_SERVER['APP_CONFIG_CACHE'] = '/tmp/bootstrap/cache/config.php';

putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
$_ENV['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';
$_SERVER['APP_SERVICES_CACHE'] = '/tmp/bootstrap/cache/services.php';

putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = '/tmp/bootstrap/cache/packages.php';

putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
$_ENV['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes.php';
$_SERVER['APP_ROUTES_CACHE'] = '/tmp/bootstrap/cache/routes.php';

// Prepare SQLite database file in /tmp if sqlite is used
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'sqlite');
$isNewSqlite = false;

if ($dbConnection === 'sqlite') {
    $sqliteDb = '/tmp/database.sqlite';
    if (! file_exists($sqliteDb) || filesize($sqliteDb) === 0) {
        touch($sqliteDb);
        $isNewSqlite = true;
    }
    putenv("DB_DATABASE={$sqliteDb}");
    $_ENV['DB_DATABASE'] = $sqliteDb;
    $_SERVER['DB_DATABASE'] = $sqliteDb;
}

// Register Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel
/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

// Auto migrate and seed default admin user on fresh SQLite instance
if ($isNewSqlite) {
    try {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
    } catch (Throwable $e) {
        // Fallback gracefully
    }
}

// Handle HTTP request
$app->handleRequest(Request::capture());
