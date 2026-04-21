<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Support;

if (!function_exists(__NAMESPACE__ . '\\finella_compat_enabled')) {
    function finella_compat_enabled(): bool
    {
        $env = getenv('APP_COMPAT');
        if ($env !== false && $env !== '') {
            return filter_var($env, FILTER_VALIDATE_BOOLEAN);
        }
        return true;
    }
}

spl_autoload_register(static function (string $class): void {
    if (!finella_compat_enabled()) {
        return;
    }

    $map = [
        'Application' => \Finella\Core\Application::class,
        'Router' => \Finella\Http\Router::class,
        'Request' => \Finella\Http\Request::class,
        'Response' => \Finella\Http\Response::class,
    ];

    if (!isset($map[$class])) {
        return;
    }

    if (class_exists($class, false)) {
        return;
    }

    if (class_exists($map[$class])) {
        class_alias($map[$class], $class);
    }
});



