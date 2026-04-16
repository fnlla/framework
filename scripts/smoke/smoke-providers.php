<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$cacheFile = $appDir . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'providers.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

if (!is_file($cacheFile)) {
    fwrite(STDERR, "Provider cache missing. Run tools/harness/bin/finella-discover.\n");
    exit(1);
}

$cached = require $cacheFile;
$providers = [];
if (is_array($cached)) {
    if (isset($cached['providers']) && is_array($cached['providers'])) {
        $providers = $cached['providers'];
    } else {
        $providers = $cached;
    }
}

$required = [
    'Finella\\Database\\DatabaseServiceProvider',
    'Finella\\Orm\\OrmServiceProvider',
    'Finella\\Cache\\CacheServiceProvider',
    'Finella\\Queue\\QueueServiceProvider',
    'Finella\\Mail\\MailServiceProvider',
    'Finella\\Session\\SessionServiceProvider',
    'Finella\\Cookie\\CookieServiceProvider',
    'Finella\\Auth\\AuthServiceProvider',
    'Finella\\Csrf\\CsrfServiceProvider',
    'Finella\\RateLimit\\RateLimitServiceProvider',
    'Finella\\SecurityHeaders\\SecurityHeadersServiceProvider',
    'Finella\\RequestLogging\\RequestLoggingServiceProvider',
    'Finella\\Log\\LogServiceProvider',
];

foreach ($required as $provider) {
    ok(in_array($provider, $providers, true), "Provider missing from cache: {$provider}");
}

echo "Provider cache smoke tests OK\n";

