<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 2);

return [
    'driver' => env('CACHE_DRIVER', 'file'),
    'path' => env('CACHE_PATH', $basePath . '/storage/cache'),
    'ttl' => (int) env('CACHE_TTL', 3600),
    'redis' => [
        'url' => env('CACHE_REDIS_URL', ''),
        'host' => env('CACHE_REDIS_HOST', '127.0.0.1'),
        'port' => (int) env('CACHE_REDIS_PORT', 6379),
        'username' => env('CACHE_REDIS_USERNAME', ''),
        'password' => env('CACHE_REDIS_PASSWORD', ''),
        'database' => (int) env('CACHE_REDIS_DB', 0),
        'prefix' => env('CACHE_PREFIX', 'finella:cache:'),
        'lock_prefix' => env('CACHE_LOCK_PREFIX', ''),
        'timeout' => (float) env('CACHE_REDIS_TIMEOUT', 1.5),
        'read_timeout' => (float) env('CACHE_REDIS_READ_TIMEOUT', 1.5),
        'persistent' => env('CACHE_REDIS_PERSISTENT', false),
        'persistent_id' => env('CACHE_REDIS_PERSISTENT_ID', ''),
    ],
];
