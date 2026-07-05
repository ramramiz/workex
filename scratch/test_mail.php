<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to send an OtpMail (OTP: 654321) to ramiz@teamtechsoul.com...\n";
    Mail::to('ramiz@teamtechsoul.com')->send(new OtpMail('654321'));
    echo "SUCCESS: OtpMail sent successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR: Failed to send OtpMail.\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
