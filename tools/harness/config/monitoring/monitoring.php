<?php

declare(strict_types=1);

return [
    'enabled' => env('Fnlla_MONITORING_ENABLED', false),
    'public' => env('Fnlla_MONITORING_PUBLIC', false),
    'access_token' => env('Fnlla_MONITORING_ACCESS_TOKEN', ''),
    'cache_ttl' => env('Fnlla_MONITORING_CACHE_TTL', 3600),
    'prefix' => env('Fnlla_MONITORING_CACHE_PREFIX', 'monitoring'),
    'recent_limit' => env('Fnlla_MONITORING_RECENT_LIMIT', 30),
    'path' => env('Fnlla_MONITORING_PATH', '/metrics'),
];
