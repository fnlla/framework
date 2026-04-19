<?php

declare(strict_types=1);

require dirname(__DIR__) . '/ensure-php85.php';

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;

use Finella\Contracts\Log\LoggerInterface;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Http\Uri;
use Finella\RequestLogging\RequestLoggerMiddleware;
use Finella\Runtime\RequestContext;
use Finella\Runtime\ResetManager;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

final class TestLogger implements LoggerInterface
{
    public array $lastContext = [];

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->lastContext = $context;
    }
}

$logger = new TestLogger();
$middleware = new RequestLoggerMiddleware($logger);

$context = new RequestContext(new ResetManager(), 'req-test-123');
$context->setHeaderFlags(true, true, true);
$context->begin();
$request = new Request('GET', new Uri('http://localhost/test'), [], null, ['REMOTE_ADDR' => '127.0.0.1']);
$response = $middleware($request, fn () => Response::text('ok'));

ok($response->getHeaderLine('X-Request-Id') === 'req-test-123', 'request id header');
ok(($logger->lastContext['request_id'] ?? '') === 'req-test-123', 'request id in log context');

$context->end();

echo "Observability smoke tests OK\n";
