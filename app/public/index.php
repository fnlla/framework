<?php

declare(strict_types=1);

use Finella\Contracts\Http\KernelInterface;
use Finella\Http\Request;
use Finella\Support\Psr\Http\Message\ResponseInterface;

require __DIR__ . '/../vendor/autoload.php';

$kernel = require __DIR__ . '/../bootstrap/app.php';

if (!$kernel instanceof KernelInterface) {
    http_response_code(500);
    echo 'Bootstrap must return a KernelInterface.';
    exit(1);
}

$warmKernelValue = getenv('APP_WARM_KERNEL');
$warmKernel = is_string($warmKernelValue) && in_array(strtolower(trim($warmKernelValue)), ['1', 'true', 'yes', 'on'], true);
if ($warmKernel && method_exists($kernel, 'boot')) {
    $kernel->boot();
}

$request = Request::fromGlobals();
try {
    $response = $kernel->handle($request);
    if (is_object($response) && method_exists($response, 'send')) {
        $response->send();
        exit(0);
    }

    if ($response instanceof ResponseInterface) {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header($name . ': ' . $value, false);
            }
        }
        echo (string) $response->getBody();
        exit(0);
    }
} catch (Throwable $e) {
    http_response_code(500);
    $debugValue = getenv('APP_DEBUG');
    $debug = is_string($debugValue) && in_array(strtolower(trim($debugValue)), ['1', 'true', 'yes', 'on'], true);
    $env = strtolower((string) getenv('APP_ENV'));
    if ($debug || $env === 'local') {
        echo 'Unhandled exception: ' . $e->getMessage();
        exit(1);
    }
    echo 'Server error';
    exit(1);
}
