<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$autoloadAvailable = is_file($autoload);

if (!$autoloadAvailable) {
    $composer = getenv('COMPOSER_BIN') ?: getenv('COMPOSER_BINARY') ?: 'composer';
    $cacheDir = getenv('COMPOSER_CACHE_DIR');
    $homeDir = getenv('COMPOSER_HOME');
    $localCache = $appDir . DIRECTORY_SEPARATOR . '.composer-cache';
    $localHome = $appDir . DIRECTORY_SEPARATOR . '.composer-home';

    if (!is_string($cacheDir) || $cacheDir === '') {
        $cacheDir = $localCache;
    }
    if (!is_string($homeDir) || $homeDir === '') {
        $homeDir = $localHome;
    }

    if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
        fwrite(STDERR, "Unable to create COMPOSER_CACHE_DIR: {$cacheDir}\n");
        exit(1);
    }
    if (!is_dir($homeDir) && !@mkdir($homeDir, 0775, true) && !is_dir($homeDir)) {
        fwrite(STDERR, "Unable to create COMPOSER_HOME: {$homeDir}\n");
        exit(1);
    }

    $composerCmd = preg_match('/\s/', $composer) ? '"' . $composer . '"' : $composer;
    $command = $composerCmd . ' install --no-interaction --prefer-dist --no-progress';

    fwrite(STDOUT, "tools/harness/vendor/autoload.php missing. Running composer install in tools/harness...\n");

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $env = array_merge($_SERVER, $_ENV);
    $cleanEnv = [];
    foreach ($env as $key => $value) {
        if (!is_string($key) || $key === '') {
            continue;
        }
        if (is_array($value) || is_object($value)) {
            continue;
        }
        if (is_bool($value)) {
            $cleanEnv[$key] = $value ? '1' : '0';
            continue;
        }
        $cleanEnv[$key] = (string) $value;
    }
    $cleanEnv['COMPOSER_CACHE_DIR'] = $cacheDir;
    $cleanEnv['COMPOSER_HOME'] = $homeDir;

    $process = proc_open($command, $descriptorSpec, $pipes, $appDir, $cleanEnv);
    if (!is_resource($process)) {
        fwrite(STDERR, "Failed to start composer install in tools/harness.\n");
        exit(1);
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        fwrite(STDERR, "composer install failed (exit {$exitCode}).\n");
        if ($stderr !== '') {
            fwrite(STDERR, $stderr . "\n");
        } elseif ($stdout !== '') {
            fwrite(STDOUT, $stdout . "\n");
        }
        exit(1);
    }

    $autoloadAvailable = is_file($autoload);
    if (!$autoloadAvailable) {
        fwrite(STDERR, "composer install completed but autoload.php is still missing.\n");
        exit(1);
    }
}

require $autoload;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$checks = [
    ['package' => \Finella\Session\SessionMiddleware::class, 'alias' => \Finella\Http\Middleware\SessionMiddleware::class],
    ['package' => \Finella\Cookie\CookieMiddleware::class, 'alias' => \Finella\Http\Middleware\CookieMiddleware::class],
    ['package' => \Finella\Auth\Middleware\AuthMiddleware::class, 'alias' => \Finella\Http\Middleware\AuthMiddleware::class],
    ['package' => \Finella\Csrf\CsrfMiddleware::class, 'alias' => \Finella\Http\Middleware\CsrfMiddleware::class],
    ['package' => \Finella\RateLimit\RateLimitMiddleware::class, 'alias' => \Finella\Http\Middleware\RateLimitMiddleware::class],
    ['package' => \Finella\RequestLogging\RequestLoggerMiddleware::class, 'alias' => \Finella\Http\Middleware\RequestLoggerMiddleware::class],
    ['package' => \Finella\SecurityHeaders\SecurityHeadersMiddleware::class, 'alias' => \Finella\Http\Middleware\SecurityHeadersMiddleware::class],

    ['package' => \Finella\Csrf\CsrfTokenManager::class, 'alias' => \Finella\Security\CsrfTokenManager::class],

    ['package' => \Finella\Auth\AuthManager::class, 'alias' => \Finella\Support\Auth\AuthManager::class],
    ['package' => \Finella\Auth\AuthServiceProvider::class, 'alias' => \Finella\Support\Auth\AuthServiceProvider::class],
    ['package' => \Finella\Auth\CallableUserProvider::class, 'alias' => \Finella\Support\Auth\CallableUserProvider::class],
    ['package' => \Finella\Auth\SessionGuard::class, 'alias' => \Finella\Support\Auth\SessionGuard::class],
    ['package' => \Finella\Auth\TokenGuard::class, 'alias' => \Finella\Support\Auth\TokenGuard::class],
    ['package' => \Finella\Auth\UserProviderInterface::class, 'alias' => \Finella\Support\Auth\UserProviderInterface::class],

    ['package' => \Finella\Cookie\Cookie::class, 'alias' => \Finella\Support\Cookie::class],
    ['package' => \Finella\Cookie\CookieJar::class, 'alias' => \Finella\Support\CookieJar::class],
    ['package' => \Finella\Cookie\CookieServiceProvider::class, 'alias' => \Finella\Support\CookieServiceProvider::class],

    ['package' => \Finella\Session\SessionInterface::class, 'alias' => \Finella\Support\SessionInterface::class],
    ['package' => \Finella\Session\SessionManager::class, 'alias' => \Finella\Support\SessionManager::class],
    ['package' => \Finella\Session\SessionServiceProvider::class, 'alias' => \Finella\Support\SessionServiceProvider::class],

    ['package' => \Finella\RateLimit\RateLimiter::class, 'alias' => \Finella\Support\RateLimiter::class],
    ['package' => \Finella\RateLimit\RateLimitServiceProvider::class, 'alias' => \Finella\Support\RateLimitServiceProvider::class],

    ['package' => \Finella\Log\Logger::class, 'alias' => \Finella\Support\Logger::class],
    ['package' => \Finella\Log\LogServiceProvider::class, 'alias' => \Finella\Support\LogServiceProvider::class],
];

$checked = 0;
$skipped = 0;

foreach ($checks as $check) {
    $package = $check['package'];
    $alias = $check['alias'];

    if (class_exists($package) || interface_exists($package)) {
        $checked++;
        ok(class_exists($alias) || interface_exists($alias), "Alias missing: {$alias} for {$package}");
        continue;
    }

    $skipped++;
}

echo "Alias smoke: checked {$checked}, skipped {$skipped}.\n";

