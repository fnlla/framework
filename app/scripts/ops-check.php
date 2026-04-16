<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Autoload file not found: {$autoload}\n");
    fwrite(STDERR, "Run composer install in app/.\n");
    exit(1);
}

require $autoload;

$bootstrap = $root . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
if (!is_file($bootstrap)) {
    fwrite(STDERR, "Bootstrap file not found: {$bootstrap}\n");
    exit(1);
}

require $bootstrap;

$app = $GLOBALS['finella_app'] ?? null;

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

$envValue = strtolower((string) getenv('APP_ENV'));
if ($envValue === '') {
    $envValue = 'prod';
}
$debugValue = strtolower((string) getenv('APP_DEBUG'));
$debugEnabled = in_array($debugValue, ['1', 'true', 'yes', 'on'], true);
$isDev = in_array($envValue, ['local', 'dev', 'development', 'test'], true) || $debugEnabled;

$results = [];
$failures = 0;
$warnings = 0;

$addResult = static function (string $label, string $status, string $detail = '') use (&$results, &$failures, &$warnings): void {
    $results[] = [$label, $status, $detail];
    if ($status === 'FAIL') {
        $failures++;
    } elseif ($status === 'WARN') {
        $warnings++;
    }
};

$adminEnabled = $toBool(getenv('ADMIN_ENABLED'), false);
$adminDevEnabled = $toBool(getenv('ADMIN_DEV_ENABLED'), false);
$adminRoutesEnabled = $adminEnabled || ($isDev && $adminDevEnabled);
$adminLoginRequired = $toBool(getenv('ADMIN_LOGIN_REQUIRED'), true);
$adminAuthAllow = $toBool(getenv('ADMIN_AUTH_ALLOW_AUTH'), false);
$adminPassword = trim((string) getenv('ADMIN_LOGIN_PASSWORD'));
$adminHash = trim((string) getenv('ADMIN_LOGIN_PASSWORD_HASH'));
$requiresHash = !$isDev && !$debugEnabled;

if (!$adminRoutesEnabled) {
    $addResult('admin-auth', 'OK', 'admin routes disabled by config');
} elseif (!$adminLoginRequired) {
    $addResult('admin-auth', 'OK', 'ADMIN_LOGIN_REQUIRED=0');
} else {
    if ($requiresHash && $adminHash === '') {
        $addResult('admin-auth', 'FAIL', 'ADMIN_LOGIN_PASSWORD_HASH required for non-dev');
    } elseif ($adminHash === '' && $adminPassword === '' && !$adminAuthAllow) {
        $addResult('admin-auth', 'WARN', 'missing ADMIN_LOGIN_PASSWORD(_HASH) and ADMIN_AUTH_ALLOW_AUTH=0');
    } elseif ($adminHash !== '') {
        $addResult('admin-auth', 'OK', 'hash configured');
    } elseif ($adminPassword !== '') {
        $addResult('admin-auth', 'WARN', 'plain password configured; add ADMIN_LOGIN_PASSWORD_HASH for prod');
    } else {
        $addResult('admin-auth', 'WARN', 'admin auth not configured');
    }
}

$docsEnabled = $toBool(getenv('DOCS_ENABLED'), true);
$docsPublic = $toBool(getenv('DOCS_PUBLIC'), false);
$docsToken = trim((string) getenv('DOCS_ACCESS_TOKEN'));

if (!$docsEnabled) {
    $addResult('docs-access', 'OK', 'DOCS_ENABLED=0');
} elseif ($isDev) {
    $addResult('docs-access', 'OK', 'dev environment');
} elseif ($docsPublic) {
    $addResult('docs-access', 'OK', 'DOCS_PUBLIC=1');
} elseif ($docsToken === '') {
    $addResult('docs-access', 'WARN', 'DOCS_ACCESS_TOKEN missing; /docs returns 404 in prod');
} else {
    $addResult('docs-access', 'OK', 'token configured');
}

$warmKernel = $toBool(getenv('APP_WARM_KERNEL'), false);
if (!$warmKernel) {
    $addResult('warm-kernel', 'OK', 'APP_WARM_KERNEL=0');
} else {
    $resetters = [];
    if ($app instanceof \Finella\Core\Container && method_exists($app, 'resetters')) {
        $resetters = $app->resetters();
    } elseif ($app instanceof \Finella\Core\Container) {
        $resetters = [];
    }
    $count = is_array($resetters) ? count($resetters) : 0;

    if ($count === 0) {
        $addResult('warm-kernel', 'WARN', 'no resetters registered; add per-request cleanup');
    } else {
        $addResult('warm-kernel', 'OK', 'resetters=' . $count);
    }
}

$headers = ['Check', 'Status', 'Details'];
$widths = array_map('strlen', $headers);
foreach ($results as $row) {
    $widths[0] = max($widths[0], strlen($row[0]));
    $widths[1] = max($widths[1], strlen($row[1]));
    $widths[2] = max($widths[2], strlen($row[2]));
}

$line = '+' . str_repeat('-', $widths[0] + 2)
    . '+' . str_repeat('-', $widths[1] + 2)
    . '+' . str_repeat('-', $widths[2] + 2) . "+\n";

echo $line;
printf("| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n", ...$headers);
echo $line;
foreach ($results as $row) {
    printf(
        "| %-" . $widths[0] . "s | %-" . $widths[1] . "s | %-" . $widths[2] . "s |\n",
        $row[0],
        $row[1],
        $row[2]
    );
}
echo $line;

$strict = $toBool(getenv('FINELLA_OPS_CHECK_STRICT'), false) || $toBool(getenv('CI'), false);
if ($failures > 0) {
    exit(1);
}
if ($strict && $warnings > 0) {
    exit(1);
}

exit(0);
