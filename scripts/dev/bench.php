<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$autoload = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    echo "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness first.\n";
    exit(0);
}

require $autoload;

use Finella\Core\Container;
use Finella\Http\Response;

final class BenchDep {}
final class BenchOpt {}
final class BenchTarget
{
    public function __construct(BenchDep $dep, ?BenchOpt $opt = null)
    {
    }
}

$container = new Container();
$iterations = 20000;

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $container->make(BenchTarget::class);
}
$containerMs = (microtime(true) - $start) * 1000.0;

$response = new Response(200, [
    'Content-Type' => 'text/plain',
    'X-Test' => '1',
    'X-Another' => '2',
]);

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $response->hasHeader('Content-Type');
    $response->getHeader('x-test');
    $response->getHeaderLine('X-Another');
}
$headersMs = (microtime(true) - $start) * 1000.0;

echo "Bench iterations: {$iterations}\n";
echo "Container resolves: " . number_format($containerMs, 2) . " ms\n";
echo "Header lookups:   " . number_format($headersMs, 2) . " ms\n";

