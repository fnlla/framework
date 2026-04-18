<?php

declare(strict_types=1);

return [
    'enabled' => env('FINELLA_MONITORING_ENABLED', false),
    'public' => env('FINELLA_MONITORING_PUBLIC', false),
    'access_token' => env('FINELLA_MONITORING_ACCESS_TOKEN', ''),
    'cache_ttl' => env('FINELLA_MONITORING_CACHE_TTL', 3600),
    'prefix' => env('FINELLA_MONITORING_CACHE_PREFIX', 'monitoring'),
    'recent_limit' => env('FINELLA_MONITORING_RECENT_LIMIT', 30),
    'path' => env('FINELLA_MONITORING_PATH', '/metrics'),
];
