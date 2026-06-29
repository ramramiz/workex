<?php
require __DIR__ . '/../vendor/autoload.php';
// Boot Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Connection: " . DB::getDefaultConnection() . "\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Alert Count: " . DB::table('app_alerts')->count() . "\n";
echo "Alert User Count: " . DB::table('app_alert_users')->count() . "\n";
