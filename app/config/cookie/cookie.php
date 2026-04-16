<?php

declare(strict_types=1);

$envBool = static function (string $key, bool $default = false): bool {
    $value = env($key, $default);
    if (is_bool($value)) {
        return $value;
    }
    if ($value === null || $value === '') {
        return $default;
    }
    $value = strtolower(trim((string) $value));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
};

$secureDefault = strtolower((string) env('APP_ENV', 'local')) === 'prod';

return [
    'path' => env('COOKIE_PATH', '/'),
    'domain' => env('COOKIE_DOMAIN', ''),
    'secure' => $envBool('COOKIE_SECURE', $secureDefault),
    'httponly' => $envBool('COOKIE_HTTPONLY', true),
    'samesite' => env('COOKIE_SAMESITE', 'Lax'),
];
