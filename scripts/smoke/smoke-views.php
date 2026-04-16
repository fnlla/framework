<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

require $appDir . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

if (!function_exists('view')) {
    fwrite(STDERR, "view() helper not available.\n");
    exit(1);
}

$response = null;
$body = '';
$candidates = ['pages/home', 'home'];
foreach ($candidates as $candidate) {
    $response = view($candidate);
    if (!$response instanceof \Finella\Http\Response) {
        continue;
    }
    $body = (string) $response->getBody();
    if (str_contains($body, 'Product entry point')) {
        break;
    }
}

ok($response instanceof \Finella\Http\Response, 'view() returns Response');
ok(str_contains($body, 'Product entry point'), 'home view renders expected content');

echo "View smoke tests OK\n";

