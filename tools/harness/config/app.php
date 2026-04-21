<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Fnlla'),
    'env' => env('APP_ENV', 'local'),
    'version' => env('APP_VERSION', 'dev'),
    'base_path' => env('APP_BASE_PATH', ''),
    'asset_url' => env('ASSET_URL', ''),
    'routes_cache' => env('ROUTES_CACHE_PATH', ''),
    'routes_cache_strict' => env('ROUTES_CACHE_STRICT', true),
    'trusted_proxies' => (static function (): array {
        $value = env('TRUSTED_PROXIES', '');
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return [];
        }
        $value = trim($value);
        if ($value === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    })(),
    'debug' => env('APP_DEBUG', false),
    'views_path' => __DIR__ . '/../resources/views',
    'timezone' => env('APP_TIMEZONE', 'UTC'),
];
