<?php

declare(strict_types=1);

require dirname(__DIR__) . '/ensure-php85.php';

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
    ['package' => \Fnlla\Session\SessionMiddleware::class, 'alias' => \Fnlla\Http\Middleware\SessionMiddleware::class],
    ['package' => \Fnlla\Cookie\CookieMiddleware::class, 'alias' => \Fnlla\Http\Middleware\CookieMiddleware::class],
    ['package' => \Fnlla\Auth\Middleware\AuthMiddleware::class, 'alias' => \Fnlla\Http\Middleware\AuthMiddleware::class],
    ['package' => \Fnlla\Csrf\CsrfMiddleware::class, 'alias' => \Fnlla\Http\Middleware\CsrfMiddleware::class],
    ['package' => \Fnlla\RateLimit\RateLimitMiddleware::class, 'alias' => \Fnlla\Http\Middleware\RateLimitMiddleware::class],
    ['package' => \Fnlla\RequestLogging\RequestLoggerMiddleware::class, 'alias' => \Fnlla\Http\Middleware\RequestLoggerMiddleware::class],
    ['package' => \Fnlla\SecurityHeaders\SecurityHeadersMiddleware::class, 'alias' => \Fnlla\Http\Middleware\SecurityHeadersMiddleware::class],

    ['package' => \Fnlla\Csrf\CsrfTokenManager::class, 'alias' => \Fnlla\Security\CsrfTokenManager::class],

    ['package' => \Fnlla\Auth\AuthManager::class, 'alias' => \Fnlla\Support\Auth\AuthManager::class],
    ['package' => \Fnlla\Auth\AuthServiceProvider::class, 'alias' => \Fnlla\Support\Auth\AuthServiceProvider::class],
    ['package' => \Fnlla\Auth\CallableUserProvider::class, 'alias' => \Fnlla\Support\Auth\CallableUserProvider::class],
    ['package' => \Fnlla\Auth\SessionGuard::class, 'alias' => \Fnlla\Support\Auth\SessionGuard::class],
    ['package' => \Fnlla\Auth\TokenGuard::class, 'alias' => \Fnlla\Support\Auth\TokenGuard::class],
    ['package' => \Fnlla\Auth\UserProviderInterface::class, 'alias' => \Fnlla\Support\Auth\UserProviderInterface::class],

    ['package' => \Fnlla\Cookie\Cookie::class, 'alias' => \Fnlla\Support\Cookie::class],
    ['package' => \Fnlla\Cookie\CookieJar::class, 'alias' => \Fnlla\Support\CookieJar::class],
    ['package' => \Fnlla\Cookie\CookieServiceProvider::class, 'alias' => \Fnlla\Support\CookieServiceProvider::class],

    ['package' => \Fnlla\Session\SessionInterface::class, 'alias' => \Fnlla\Support\SessionInterface::class],
    ['package' => \Fnlla\Session\SessionManager::class, 'alias' => \Fnlla\Support\SessionManager::class],
    ['package' => \Fnlla\Session\SessionServiceProvider::class, 'alias' => \Fnlla\Support\SessionServiceProvider::class],

    ['package' => \Fnlla\RateLimit\RateLimiter::class, 'alias' => \Fnlla\Support\RateLimiter::class],
    ['package' => \Fnlla\RateLimit\RateLimitServiceProvider::class, 'alias' => \Fnlla\Support\RateLimitServiceProvider::class],

    ['package' => \Fnlla\Log\Logger::class, 'alias' => \Fnlla\Support\Logger::class],
    ['package' => \Fnlla\Log\LogServiceProvider::class, 'alias' => \Fnlla\Support\LogServiceProvider::class],
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
