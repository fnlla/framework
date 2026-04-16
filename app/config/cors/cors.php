<?php

declare(strict_types=1);

return [
    'enabled' => env('CORS_ENABLED', false),
    'allowed_origins' => (function () {
        $raw = env('CORS_ALLOWED_ORIGINS', '');
        if (!is_string($raw)) {
            return [];
        }
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        if ($raw === '*' || strtolower($raw) === 'all') {
            return ['*'];
        }
        $items = array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $value): bool => $value !== ''));
        return $items;
    })(),
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => [],
    'allow_credentials' => env('CORS_ALLOW_CREDENTIALS', false),
    'max_age' => 600,
];
