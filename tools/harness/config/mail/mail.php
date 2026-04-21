<?php

declare(strict_types=1);

return [
    'dsn' => env('MAIL_DSN', ''),
    'host' => env('MAIL_HOST', 'localhost'),
    'port' => (int) env('MAIL_PORT', 1025),
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'encryption' => env('MAIL_ENCRYPTION', ''),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.test'),
        'name' => env('MAIL_FROM_NAME', 'Fnlla'),
    ],
];
