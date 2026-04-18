<?php

declare(strict_types=1);

return [
    'warm_kernel' => env('FINELLA_WARM_KERNEL', false),
    'routes_cache_enabled' => env('ROUTES_CACHE_ENABLED', true),
    'routes_cache_envs' => (static function (): array {
        $value = env('ROUTES_CACHE_ENVS', 'prod,staging');
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
];
