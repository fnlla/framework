<?php

declare(strict_types=1);

require dirname(__DIR__) . '/ensure-php85.php';

$root = dirname(__DIR__, 2);
$packagesDir = $root . DIRECTORY_SEPARATOR . 'packages';
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$autoloadAvailable = is_file($autoload);

if (!is_dir($packagesDir)) {
    fwrite(STDERR, "Packages directory not found: {$packagesDir}\n");
    exit(1);
}

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

    $baseEnv = array_merge($_SERVER, $_ENV);
    $filteredEnv = [];
    foreach ($baseEnv as $key => $value) {
        if (is_scalar($value) || $value === null) {
            $filteredEnv[$key] = (string) $value;
        }
    }

    $env = array_merge($filteredEnv, [
        'COMPOSER_CACHE_DIR' => $cacheDir,
        'COMPOSER_HOME' => $homeDir,
    ]);

    $process = proc_open($command, $descriptorSpec, $pipes, $appDir, $env);
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

$packages = glob($packagesDir . DIRECTORY_SEPARATOR . '*') ?: [];
sort($packages);

$results = [];
$hasFailures = false;

$addResult = function (string $label, string $status, string $details) use (&$results, &$hasFailures): void {
    $results[] = [
        'package' => $label,
        'status' => $status,
        'details' => $details,
    ];
    if ($status === 'FAIL') {
        $hasFailures = true;
    }
};

$runCommand = function (string $command, string $cwd) {
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $cwd);
    if (!is_resource($process)) {
        return [1, 'unable to start process'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    $output = trim((string) ($stderr !== '' ? $stderr : $stdout));
    return [$exitCode, $output];
};

$aiSmoke = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'smoke' . DIRECTORY_SEPARATOR . 'smoke-ai.php';
if (is_file($aiSmoke)) {
    [$exitCode, $output] = $runCommand(PHP_BINARY . ' ' . escapeshellarg($aiSmoke), $root);
    $details = $exitCode === 0 ? 'exit 0' : 'exit ' . $exitCode;
    if ($output !== '') {
        $lines = preg_split('/\r?\n/', $output) ?: [];
        $details .= ' - ' . ($lines[0] ?? '');
    }
    $addResult('ai-smoke', $exitCode === 0 ? 'OK' : 'FAIL', $details);
}

$routesCacheSmoke = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'smoke' . DIRECTORY_SEPARATOR . 'smoke-routes-cache.php';
if (is_file($routesCacheSmoke)) {
    [$exitCode, $output] = $runCommand(PHP_BINARY . ' ' . escapeshellarg($routesCacheSmoke), $root);
    $details = $exitCode === 0 ? 'exit 0' : 'exit ' . $exitCode;
    if ($output !== '') {
        $lines = preg_split('/\r?\n/', $output) ?: [];
        $details .= ' - ' . ($lines[0] ?? '');
    }
    $addResult('routes-cache-smoke', $exitCode === 0 ? 'OK' : 'FAIL', $details);
}

$mojibakeScript = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ci' . DIRECTORY_SEPARATOR . 'check-mojibake.php';
if (is_file($mojibakeScript)) {
    [$exitCode, $output] = $runCommand(PHP_BINARY . ' ' . escapeshellarg($mojibakeScript), $root);
    $details = $exitCode === 0 ? 'exit 0' : 'exit ' . $exitCode;
    if ($output !== '') {
        $lines = preg_split('/\r?\n/', $output) ?: [];
        $details .= ' - ' . ($lines[0] ?? '');
    }
    $addResult('check-mojibake', $exitCode === 0 ? 'OK' : 'FAIL', $details);
}

$docsSyncScript = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ci' . DIRECTORY_SEPARATOR . 'check-docs-sync.php';
if (is_file($docsSyncScript)) {
    [$exitCode, $output] = $runCommand(PHP_BINARY . ' ' . escapeshellarg($docsSyncScript), $root);
    $details = $exitCode === 0 ? 'exit 0' : 'exit ' . $exitCode;
    if ($output !== '') {
        $lines = preg_split('/\r?\n/', $output) ?: [];
        $details .= ' - ' . ($lines[0] ?? '');
    }
    $addResult('docs-sync', $exitCode === 0 ? 'OK' : 'FAIL', $details);
}

$securityCheck = function () use ($root, $autoload, $addResult): void {
    $appCandidates = [
        $root . DIRECTORY_SEPARATOR . 'app',
        $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness',
    ];

    $appRoot = null;
    foreach ($appCandidates as $candidate) {
        if (is_dir($candidate)) {
            $appRoot = $candidate;
            break;
        }
    }

    if (!is_string($appRoot)) {
        $addResult('security-sanity', 'SKIP', 'missing app config');
        return;
    }

    $helpers = $appRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'helpers.php';
    $configPath = $appRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'http.php';
    if (!is_file($configPath)) {
        $legacyPath = $appRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'http.php';
        if (is_file($legacyPath)) {
            $configPath = $legacyPath;
        }
    }

    if (!is_file($helpers) || !is_file($configPath)) {
        $addResult('security-sanity', 'SKIP', 'missing app config');
        return;
    }

    if (is_file($autoload)) {
        require_once $autoload;
    }
    require_once $helpers;

    $keys = ['APP_ENV', 'SECURITY_HEADERS_ENABLED', 'REQUEST_LOGGING_ENABLED', 'APP_DEBUG'];
    $backup = [];
    foreach ($keys as $key) {
        $backup[$key] = [
            'env' => getenv($key),
            'has_env' => array_key_exists($key, $_ENV),
            'env_val' => $_ENV[$key] ?? null,
            'has_server' => array_key_exists($key, $_SERVER),
            'server_val' => $_SERVER[$key] ?? null,
        ];
    }

    $setEnv = static function (string $key, string $value): void {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    };

    $setEnv('APP_ENV', 'local');
    $setEnv('SECURITY_HEADERS_ENABLED', '1');
    $setEnv('REQUEST_LOGGING_ENABLED', '1');
    $setEnv('APP_DEBUG', '0');

    $http = require $configPath;
    $global = $http['global'] ?? [];
    $securityClass = 'Finella\\SecurityHeaders\\SecurityHeadersMiddleware';
    $loggingClass = 'Finella\\RequestLogging\\RequestLoggerMiddleware';

    $hasSecurity = in_array($securityClass, $global, true);
    $hasLogging = in_array($loggingClass, $global, true);

    foreach ($keys as $key) {
        $state = $backup[$key];
        if ($state['env'] === false) {
            putenv($key);
        } else {
            putenv($key . '=' . $state['env']);
        }
        if ($state['has_env']) {
            $_ENV[$key] = $state['env_val'];
        } else {
            unset($_ENV[$key]);
        }
        if ($state['has_server']) {
            $_SERVER[$key] = $state['server_val'];
        } else {
            unset($_SERVER[$key]);
        }
    }

    if (!$hasSecurity || !$hasLogging) {
        $missing = [];
        if (!$hasSecurity) {
            $missing[] = 'SecurityHeadersMiddleware';
        }
        if (!$hasLogging) {
            $missing[] = 'RequestLoggerMiddleware';
        }
        $addResult('security-sanity', 'FAIL', 'missing ' . implode(', ', $missing));
        return;
    }

    $addResult('security-sanity', 'OK', 'defaults enabled');
};

$securityCheck();

foreach ($packages as $packagePath) {
    if (!is_dir($packagePath)) {
        continue;
    }

    $packageName = basename($packagePath);
    $testPath = $packagePath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'smoke.php';

    if (!is_file($testPath)) {
        $results[] = [
            'package' => $packageName,
            'status' => 'SKIP',
            'details' => 'smoke test not configured',
        ];
        continue;
    }

    if (!$autoloadAvailable) {
        $results[] = [
            'package' => $packageName,
            'status' => 'FAIL',
            'details' => 'missing tools/harness/vendor/autoload.php',
        ];
        $hasFailures = true;
        continue;
    }

    $bootstrap = sprintf(
        "require %s; chdir(%s); require %s;",
        var_export($autoload, true),
        var_export($packagePath, true),
        var_export($testPath, true)
    );

    $command = PHP_BINARY . ' -r ' . escapeshellarg($bootstrap);

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $packagePath);
    if (!is_resource($process)) {
        $results[] = [
            'package' => $packageName,
            'status' => 'FAIL',
            'details' => 'unable to start process',
        ];
        $hasFailures = true;
        continue;
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    $status = $exitCode === 0 ? 'OK' : 'FAIL';
    $details = $exitCode === 0 ? 'exit 0' : 'exit ' . $exitCode;

    $output = trim((string) ($stderr !== '' ? $stderr : $stdout));
    if ($output !== '') {
        $lines = preg_split('/\r?\n/', $output) ?: [];
        $firstLine = $lines[0] ?? '';
        if ($firstLine !== '') {
            $details .= ' - ' . $firstLine;
        }
    }

    $results[] = [
        'package' => $packageName,
        'status' => $status,
        'details' => $details,
    ];

    if ($status === 'FAIL') {
        $hasFailures = true;
    }
}

if ($results === []) {
    echo "No packages found.\n";
    exit(1);
}

$headers = ['Package', 'Status', 'Details'];
$widths = array_map('strlen', $headers);

foreach ($results as $row) {
    $widths[0] = max($widths[0], strlen($row['package']));
    $widths[1] = max($widths[1], strlen($row['status']));
    $widths[2] = max($widths[2], strlen($row['details']));
}

$line = '+' . str_repeat('-', $widths[0] + 2)
    . '+' . str_repeat('-', $widths[1] + 2)
    . '+' . str_repeat('-', $widths[2] + 2) . "+\n";

echo $line;
printf(
    "| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n",
    $headers[0],
    $headers[1],
    $headers[2]
);
echo $line;

foreach ($results as $row) {
    printf(
        "| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n",
        $row['package'],
        $row['status'],
        $row['details']
    );
}

echo $line;

exit($hasFailures ? 1 : 0);
