<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 2);

return [
    'path' => env('LOG_PATH', $basePath . '/storage/logs/app.log'),
    'level' => env('LOG_LEVEL', 'info'),
    'format' => env('LOG_FORMAT', 'line'),
    'include_request_id' => env('LOG_REQUEST_ID', true),
    'context' => array_filter([
        'app' => env('APP_NAME', 'Fnlla'),
        'env' => env('APP_ENV', 'local'),
        'version' => env('APP_VERSION', ''),
    ], static fn ($value) => $value !== null && $value !== ''),
];

