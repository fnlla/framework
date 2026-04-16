<?php

declare(strict_types=1);

$toBool = static function (mixed $value, bool $default = false): bool {
    if (is_bool($value)) {
        return $value;
    }
    if (is_int($value)) {
        return $value === 1;
    }
    if (!is_string($value)) {
        return $default;
    }

    $normalized = strtolower(trim($value));
    if ($normalized === '') {
        return $default;
    }

    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
};

$envValue = strtolower((string) env('APP_ENV', 'prod'));
$isProd = in_array($envValue, ['prod', 'production'], true);

return [
    'enabled' => $isProd ? true : $toBool(env('AI_REDACTION_ENABLED', true), true),
    'mask' => env('AI_REDACTION_MASK', '[REDACTED]'),
    'patterns' => [
        '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i',
        '/\\bsk-[A-Za-z0-9]{16,}\\b/',
        '/\\b(?:api|secret|token|key)[=:\\s]+[A-Za-z0-9_\\-]{8,}\\b/i',
        '/\\b(?:\\d[ -]*?){13,16}\\b/',
    ],
];
