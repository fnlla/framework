<?php

declare(strict_types=1);

return [
    'driver' => env('QUEUE_DRIVER', 'sync'),
    'queue' => env('QUEUE_NAME', 'default'),
    'payload_secret' => env('QUEUE_PAYLOAD_SECRET', ''),
    'allowed_job_classes' => env('QUEUE_ALLOWED_JOBS', '*'),
    'database' => [
        'table' => env('QUEUE_TABLE', 'queue_jobs'),
        'failed_table' => env('QUEUE_FAILED_TABLE', 'queue_failed_jobs'),
        'max_attempts' => (int) env('QUEUE_TRIES', 3),
        'retry_after' => (int) env('QUEUE_RETRY_AFTER', 60),
        'backoff' => env('QUEUE_BACKOFF', ''),
    ],
    'redis' => [
        'url' => env('QUEUE_REDIS_URL', ''),
        'host' => env('QUEUE_REDIS_HOST', '127.0.0.1'),
        'port' => (int) env('QUEUE_REDIS_PORT', 6379),
        'username' => env('QUEUE_REDIS_USERNAME', ''),
        'password' => env('QUEUE_REDIS_PASSWORD', ''),
        'database' => (int) env('QUEUE_REDIS_DB', 0),
        'prefix' => env('QUEUE_REDIS_PREFIX', 'finella:queue:'),
        'queue' => env('QUEUE_NAME', 'default'),
        'timeout' => (float) env('QUEUE_REDIS_TIMEOUT', 1.5),
        'read_timeout' => (float) env('QUEUE_REDIS_READ_TIMEOUT', 1.5),
        'persistent' => env('QUEUE_REDIS_PERSISTENT', false),
        'persistent_id' => env('QUEUE_REDIS_PERSISTENT_ID', ''),
        'max_attempts' => (int) env('QUEUE_TRIES', 3),
        'retry_after' => (int) env('QUEUE_RETRY_AFTER', 60),
        'backoff' => env('QUEUE_BACKOFF', ''),
    ],
];
