<?php

declare(strict_types=1);

return [
    'enabled' => env('MONITORING_ENABLED', false),
    'public' => env('MONITORING_PUBLIC', false),
    'access_token' => env('MONITORING_ACCESS_TOKEN', ''),
    'cache_ttl' => env('MONITORING_CACHE_TTL', 3600),
    'prefix' => env('MONITORING_CACHE_PREFIX', 'monitoring'),
    'recent_limit' => env('MONITORING_RECENT_LIMIT', 30),
    'path' => env('MONITORING_PATH', '/metrics'),
];
