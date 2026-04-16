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

$results = [];
$failures = 0;

$addResult = static function (string $label, string $status, string $detail = '') use (&$results, &$failures): void {
    $results[] = [$label, $status, $detail];
    if ($status === 'FAIL') {
        $failures++;
    }
};

$env = strtolower(trim((string) getenv('APP_ENV')));
$debug = $toBool(getenv('APP_DEBUG'), false);

if ($env === 'prod') {
    $addResult('app-env', 'OK', 'APP_ENV=prod');
} else {
    $addResult('app-env', 'FAIL', 'APP_ENV must be prod (current: ' . ($env === '' ? '(empty)' : $env) . ')');
}

if ($debug) {
    $addResult('app-debug', 'FAIL', 'APP_DEBUG must be 0 in production');
} else {
    $addResult('app-debug', 'OK', 'APP_DEBUG=0');
}

$docsEnabled = $toBool(getenv('DOCS_ENABLED'), true);
if (!$docsEnabled) {
    $addResult('docs-policy', 'OK', 'DOCS_ENABLED=0');
} else {
    $docsPublic = $toBool(getenv('DOCS_PUBLIC'), false);
    $docsToken = trim((string) getenv('DOCS_ACCESS_TOKEN'));
    if ($docsPublic) {
        $addResult('docs-policy', 'FAIL', 'DOCS_PUBLIC=1 is not allowed in production profile');
    } elseif ($docsToken === '') {
        $addResult('docs-policy', 'FAIL', 'DOCS_ACCESS_TOKEN is required when DOCS_ENABLED=1');
    } else {
        $addResult('docs-policy', 'OK', 'private docs + token configured');
    }
}

$adminEnabled = $toBool(getenv('ADMIN_ENABLED'), false);
$adminLoginRequired = $toBool(getenv('ADMIN_LOGIN_REQUIRED'), true);
$adminAuthAllow = $toBool(getenv('ADMIN_AUTH_ALLOW_AUTH'), false);
$adminEmail = trim((string) getenv('ADMIN_LOGIN_EMAIL'));
$adminHash = trim((string) getenv('ADMIN_LOGIN_PASSWORD_HASH'));
$adminPassword = trim((string) getenv('ADMIN_LOGIN_PASSWORD'));

if (!$adminEnabled) {
    $addResult('admin-policy', 'OK', 'ADMIN_ENABLED=0');
} else {
    $adminFailures = [];
    if (!$adminLoginRequired) {
        $adminFailures[] = 'ADMIN_LOGIN_REQUIRED must be 1';
    }
    if ($adminAuthAllow) {
        $adminFailures[] = 'ADMIN_AUTH_ALLOW_AUTH must be 0';
    }
    if ($adminEmail === '') {
        $adminFailures[] = 'ADMIN_LOGIN_EMAIL is required';
    }
    if ($adminHash === '') {
        $adminFailures[] = 'ADMIN_LOGIN_PASSWORD_HASH is required';
    }
    if ($adminPassword !== '') {
        $adminFailures[] = 'ADMIN_LOGIN_PASSWORD must be empty when hash is used';
    }

    if ($adminFailures === []) {
        $addResult('admin-policy', 'OK', 'admin login hardened');
    } else {
        $addResult('admin-policy', 'FAIL', implode('; ', $adminFailures));
    }
}

$warmKernel = $toBool(getenv('APP_WARM_KERNEL'), false);
if ($warmKernel) {
    $addResult('warm-kernel', 'OK', 'APP_WARM_KERNEL=1');
} else {
    $addResult('warm-kernel', 'FAIL', 'APP_WARM_KERNEL must be 1 for production profile');
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

exit($failures > 0 ? 1 : 0);
