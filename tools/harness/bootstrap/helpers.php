<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return env_parse_value($_ENV[$key]);
        }

        if (array_key_exists($key, $_SERVER)) {
            return env_parse_value($_SERVER[$key]);
        }

        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        return env_parse_value($value);
    }
}

if (!function_exists('env_parse_value')) {
    function env_parse_value(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $normalized = strtolower(trim($value));
        return match ($normalized) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}
