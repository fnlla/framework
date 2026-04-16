<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Core\ConfigRepository;
use Finella\Core\Container;
use Finella\Cors\CorsMiddleware;

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

$middleware = new CorsMiddleware($config);
ok($middleware instanceof CorsMiddleware, 'CorsMiddleware created');

echo "CORS smoke tests OK\n";
