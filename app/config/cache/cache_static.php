<?php

declare(strict_types=1);

return [
    'enabled' => env('CACHE_STATIC_ENABLED', false),
    'path' => env('CACHE_STATIC_PATH', 'storage/cache/static'),
    'ttl' => env('CACHE_STATIC_TTL', 3600),
    'exclude' => [
        '/admin',
        '/api',
    ],
    'ignore_query' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ],
];
