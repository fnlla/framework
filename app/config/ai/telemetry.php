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
$defaultMaxChars = $isProd ? 4000 : 8000;
$maxChars = (int) env('AI_TELEMETRY_MAX_CHARS', $defaultMaxChars);
if ($maxChars <= 0) {
    $maxChars = $defaultMaxChars;
}

return [
    'enabled' => $toBool(env('AI_TELEMETRY_ENABLED', false), false),
    'table' => env('AI_TELEMETRY_TABLE', 'ai_runs'),
    'store_input' => env('AI_TELEMETRY_STORE_INPUT', true),
    'store_output' => env('AI_TELEMETRY_STORE_OUTPUT', true),
    'store_context' => env('AI_TELEMETRY_STORE_CONTEXT', false),
    'store_sources' => env('AI_TELEMETRY_STORE_SOURCES', false),
    'max_chars' => $maxChars,
];
