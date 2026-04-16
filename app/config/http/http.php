<?php

declare(strict_types=1);

use App\Middleware\DebugbarMiddleware as AppDebugbarMiddleware;
use Finella\Auth\Middleware\AuthMiddleware;
use Finella\Auth\Middleware\GuestMiddleware;
use Finella\CacheStatic\StaticCacheMiddleware;
use Finella\Cookie\CookieMiddleware;
use Finella\Cors\CorsMiddleware;
use Finella\Csrf\CsrfMiddleware;
use Finella\Debugbar\DebugbarCollector;
use Finella\Debugbar\Middleware\DebugbarMiddleware as FinellaDebugbarMiddleware;
use Finella\Forms\HoneypotMiddleware;
use Finella\Maintenance\MaintenanceMiddleware;
use Finella\Monitoring\MonitoringMiddleware;
use Finella\Redirects\RedirectsMiddleware;
use Finella\RequestLogging\RequestLoggerMiddleware;
use Finella\SecurityHeaders\SecurityHeadersMiddleware;
use Finella\Session\SessionMiddleware;
use Finella\Tenancy\TenantMiddleware;

$env = (string) env('APP_ENV', 'local');
$debug = env('APP_DEBUG', false);
$debugEnabled = $debug === true || $debug === 1 || $debug === '1';

$debugbarMiddleware = null;
if (class_exists(AppDebugbarMiddleware::class)) {
    $debugbarMiddleware = AppDebugbarMiddleware::class;
} elseif (class_exists(FinellaDebugbarMiddleware::class)) {
    $debugbarMiddleware = FinellaDebugbarMiddleware::class;
}

$debugEnabled = $debugEnabled && $debugbarMiddleware !== null && class_exists(DebugbarCollector::class);

$toBool = function (mixed $value, bool $default = false): bool {
    if (is_bool($value)) {
        return $value;
    }
    if (is_int($value)) {
        return $value === 1;
    }
    if (is_string($value)) {
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
    return $default;
};

$securityEnabled = $toBool(env('SECURITY_HEADERS_ENABLED', true), true);
$corsEnabled = $toBool(env('CORS_ENABLED', false), false);
$maintenanceEnabled = $toBool(env('MAINTENANCE_MODE', false), false);
$requestLoggingEnabled = $toBool(env('REQUEST_LOGGING_ENABLED', $env !== 'test'), $env !== 'test');
$csrfEnabled = $toBool(env('CSRF_ENABLED', true), true);
$formsEnabled = $toBool(env('FORMS_HONEYPOT_ENABLED', true), true);
$redirectsEnabled = $toBool(env('REDIRECTS_ENABLED', true), true);
$staticCacheEnabled = $toBool(env('CACHE_STATIC_ENABLED', false), false);
$tenancyEnabled = $toBool(env('TENANCY_ENABLED', false), false);
$requestIdHeaderEnabled = $toBool(env('REQUEST_ID_HEADER', true), true);
$traceIdHeaderEnabled = $toBool(env('TRACE_ID_HEADER', true), true);
$spanIdHeaderEnabled = $toBool(env('SPAN_ID_HEADER', true), true);
$monitoringEnabled = $toBool(env('MONITORING_ENABLED', false), false);

$rateEnabled = $toBool(env('RATE_LIMIT_ENABLED', $env === 'prod'), $env === 'prod');
$rateMax = (int) env('RATE_LIMIT_MAX', 120);
$rateMinutes = (int) env('RATE_LIMIT_MINUTES', 1);
$rateKey = (string) env('RATE_LIMIT_KEY', 'ip');
$rateSpec = 'rate:' . max(1, $rateMax) . ',' . max(1, $rateMinutes) . ',' . $rateKey;

$missing = [];
$warnMissing = function (bool $enabled, string $class, string $feature) use (&$missing): void {
    if ($enabled && !class_exists($class)) {
        $missing[] = 'Config enables ' . $feature . ' but package class is missing: ' . $class;
    }
};

$aliases = [];
if (class_exists(AuthMiddleware::class)) {
    $aliases['auth'] = AuthMiddleware::class;
}
if (class_exists(GuestMiddleware::class)) {
    $aliases['guest'] = GuestMiddleware::class;
}
if (class_exists(SessionMiddleware::class)) {
    $aliases['session'] = SessionMiddleware::class;
}
if (class_exists(CookieMiddleware::class)) {
    $aliases['cookies'] = CookieMiddleware::class;
}
if (class_exists(CsrfMiddleware::class)) {
    $aliases['csrf'] = CsrfMiddleware::class;
}
if (class_exists(TenantMiddleware::class)) {
    $aliases['tenant'] = TenantMiddleware::class;
}

$warnMissing($securityEnabled, SecurityHeadersMiddleware::class, 'security headers');
$warnMissing($corsEnabled, CorsMiddleware::class, 'CORS');
$warnMissing($maintenanceEnabled, MaintenanceMiddleware::class, 'maintenance mode');
$warnMissing($requestLoggingEnabled, RequestLoggerMiddleware::class, 'request logging');
$warnMissing($csrfEnabled, CsrfMiddleware::class, 'CSRF');
$warnMissing($formsEnabled, HoneypotMiddleware::class, 'forms honeypot');
$warnMissing($redirectsEnabled, RedirectsMiddleware::class, 'redirects');
$warnMissing($staticCacheEnabled, StaticCacheMiddleware::class, 'static cache');
$warnMissing($tenancyEnabled, TenantMiddleware::class, 'tenancy');
$warnMissing($rateEnabled, \Finella\RateLimit\RateLimiter::class, 'rate limiting');
$warnMissing($debugEnabled, DebugbarCollector::class, 'debugbar');
$warnMissing($monitoringEnabled, MonitoringMiddleware::class, 'monitoring');

if ($missing !== []) {
    foreach ($missing as $message) {
        error_log($message);
    }
}

return [
    'request_id_header' => $requestIdHeaderEnabled,
    'trace_id_header' => $traceIdHeaderEnabled,
    'span_id_header' => $spanIdHeaderEnabled,
    'middleware_aliases' => $aliases,
    'global' => array_values(array_filter([
        $maintenanceEnabled && class_exists(MaintenanceMiddleware::class) ? MaintenanceMiddleware::class : null,
        $tenancyEnabled && class_exists(TenantMiddleware::class) ? TenantMiddleware::class : null,
        $redirectsEnabled && class_exists(RedirectsMiddleware::class) ? RedirectsMiddleware::class : null,
        $staticCacheEnabled && class_exists(StaticCacheMiddleware::class) ? StaticCacheMiddleware::class : null,
        $corsEnabled && class_exists(CorsMiddleware::class) ? CorsMiddleware::class : null,
        $requestLoggingEnabled ? RequestLoggerMiddleware::class : null,
        $securityEnabled ? SecurityHeadersMiddleware::class : null,
        $debugEnabled ? $debugbarMiddleware : null,
        $monitoringEnabled && class_exists(MonitoringMiddleware::class) ? MonitoringMiddleware::class : null,
    ])),
    'middleware_groups' => [
        'web' => array_values(array_filter([
            SessionMiddleware::class,
            CookieMiddleware::class,
            $formsEnabled && class_exists(HoneypotMiddleware::class) ? HoneypotMiddleware::class : null,
            $csrfEnabled ? CsrfMiddleware::class : null,
            $rateEnabled ? $rateSpec : null,
        ])),
    ],
];