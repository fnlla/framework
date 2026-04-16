<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Debugbar\DebugbarCollector;
use Finella\Debugbar\Middleware\DebugbarMiddleware;
use Finella\Http\Request;
use Finella\Http\Response;
use Finella\Support\Psr\Http\Message\ResponseInterface;
use Finella\Support\Psr\Http\Message\ServerRequestInterface;
use Finella\Support\Psr\Http\Server\RequestHandlerInterface;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
}

DebugbarCollector::init();
DebugbarCollector::addQuery('select 1', [], 1.2);
DebugbarCollector::addMessage('info', 'hello');
DebugbarCollector::addError('Error', 'boom', __FILE__, __LINE__);
DebugbarCollector::mark('boot');

ok(count(DebugbarCollector::queries()) === 1, 'query collected');
ok(count(DebugbarCollector::messages()) === 1, 'message collected');
ok(count(DebugbarCollector::errors()) === 1, 'error collected');
ok(count(DebugbarCollector::timeline()) >= 1, 'timeline collected');

$middleware = new DebugbarMiddleware();
$request = Request::fromGlobals();

$handler = new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        DebugbarCollector::addQuery('select 2', ['id' => 1], 12.3, 1, 'execute');
        DebugbarCollector::addMessage('info', 'handler message');
        DebugbarCollector::addError('RuntimeException', 'handler error', __FILE__, __LINE__);
        DebugbarCollector::mark('handler.done');
        return Response::html('<!doctype html><html><body><h1>ok</h1></body></html>');
    }
};

$response = $middleware->process($request, $handler);
ok($response instanceof Response, 'response type');
ok($response->getHeaderLine('X-Debug-Queries') === '1', 'X-Debug-Queries');
ok($response->getHeaderLine('X-Debug-Messages') === '1', 'X-Debug-Messages');
ok($response->getHeaderLine('X-Debug-Errors') === '1', 'X-Debug-Errors');
ok($response->getHeaderLine('X-Debug-Time-Ms') !== '', 'X-Debug-Time-Ms');
ok(str_contains((string) $response->getBody(), 'Finella Debugbar'), 'debugbar panel injected');

echo "Debugbar smoke tests OK\n";
