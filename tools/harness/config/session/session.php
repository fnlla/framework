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

$basePath = dirname(__DIR__, 2);
$secureDefault = strtolower((string) env('APP_ENV', 'local')) === 'prod';

return [
    'ttl' => (int) env('SESSION_TTL', 7200),
    'path' => env('SESSION_PATH', $basePath . '/storage/sessions'),
    'cookie' => [
        'name' => env('SESSION_COOKIE', 'Fnlla_session'),
        'path' => env('COOKIE_PATH', '/'),
        'domain' => env('SESSION_DOMAIN', ''),
        'secure' => $envBool('SESSION_SECURE', $secureDefault),
        'httponly' => $envBool('SESSION_HTTPONLY', true),
        'samesite' => env('SESSION_SAMESITE', 'Lax'),
    ],
];

