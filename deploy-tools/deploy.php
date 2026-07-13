<?php

/**
 * One-time web-based deploy runner for hosts without terminal/SSH access.
 *
 * HOW TO USE
 * 1. Change $secret below to a long random string before uploading.
 * 2. Upload this file into the SAME folder as your Laravel app's real index.php
 *    (i.e. the "public" folder that your domain points to).
 * 3. Visit: https://ecosystemtest.org/deploy.php?key=YOUR_SECRET&action=migrate
 *    Allowed actions: migrate, storage-link, cache-clear, config-cache, optimize, status
 * 4. DELETE THIS FILE from the server as soon as you're done. Do not leave it deployed.
 */

$secret = 'CHANGE_THIS_TO_A_LONG_RANDOM_STRING';

$key = $_GET['key'] ?? '';
if (!hash_equals($secret, $key)) {
    http_response_code(403);
    exit('Forbidden');
}

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

$action = $_GET['action'] ?? 'status';

$run = function (string $command, array $params = []) {
    echo "=== php artisan {$command} ===\n";
    $exit = Illuminate\Support\Facades\Artisan::call($command, $params);
    echo Illuminate\Support\Facades\Artisan::output();
    echo "(exit code: {$exit})\n\n";
};

switch ($action) {
    case 'migrate':
        $run('migrate', ['--force' => true]);
        break;
    case 'storage-link':
        $run('storage:link');
        break;
    case 'cache-clear':
        $run('config:clear');
        $run('cache:clear');
        $run('route:clear');
        $run('view:clear');
        break;
    case 'config-cache':
        $run('config:cache');
        $run('route:cache');
        $run('view:cache');
        break;
    case 'optimize':
        $run('optimize');
        break;
    case 'status':
        $run('migrate:status');
        $run('about');
        break;
    default:
        http_response_code(400);
        echo "Unknown action: {$action}\n";
        echo "Allowed: migrate, storage-link, cache-clear, config-cache, optimize, status\n";
}
