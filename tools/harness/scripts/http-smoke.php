<?php

declare(strict_types=1);

use Finella\Contracts\Http\KernelInterface;
use Finella\Http\Request;
use Finella\Http\Uri;

require __DIR__ . '/../vendor/autoload.php';

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$kernel = require __DIR__ . '/../bootstrap/app.php';
ok($kernel instanceof KernelInterface, 'bootstrap/app.php must return KernelInterface');

$makeRequest = static function (string $path): Request {
    return new Request('GET', new Uri('http://localhost' . $path));
};

$response = $kernel->handle($makeRequest('/'));
ok($response->getStatusCode() === 200, 'GET / should return 200');

$response = $kernel->handle($makeRequest('/_missing'));
ok($response->getStatusCode() === 404, 'GET /_missing should return 404');

echo "OK\n";
