<?php

declare(strict_types=1);

return [
    'enabled' => env('SENTRY_ENABLED', false),
    'dsn' => env('SENTRY_DSN', ''),
    'environment' => env('SENTRY_ENV', env('APP_ENV', 'local')),
    'release' => env('SENTRY_RELEASE', ''),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),
];
