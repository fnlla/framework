<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Core\ConfigRepository;
use Finella\Core\Container;
use Finella\Runtime\RequestContext;
use Finella\Runtime\ResetManager;
use Finella\SecurityHeaders\SecurityHeadersMiddleware;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        exit(1);
    }
}

$app = new Container();
$configRepo = ConfigRepository::fromRoot(getcwd());
$config = $configRepo;
$context = new RequestContext(new ResetManager(), 'req-1', microtime(true));

$middleware = new SecurityHeadersMiddleware($config, $context);
ok($middleware instanceof SecurityHeadersMiddleware, 'SecurityHeadersMiddleware created');

echo "Security headers smoke tests OK\n";
