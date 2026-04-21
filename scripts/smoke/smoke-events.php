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

use Fnlla\Core\Container;
use Fnlla\Support\EventDispatcher;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$container = new Container();
$dispatcher = new EventDispatcher($container);

$hits = 0;
$dispatcher->listen('user.registered', function (string $event, array $payload) use (&$hits): void {
    $hits++;
});
$dispatcher->listen('user.*', function () use (&$hits): void {
    $hits++;
});

$dispatcher->dispatch('user.registered', ['id' => 1]);
ok($hits === 2, 'event + wildcard listeners executed');

$afterCommitCalled = false;
$afterDispatchCalled = false;
$dispatcher->setAfterCommitHandler(function (callable $callback) use (&$afterCommitCalled): void {
    $afterCommitCalled = true;
    $callback();
});
$dispatcher->listen('order.paid', function () use (&$afterDispatchCalled): void {
    $afterDispatchCalled = true;
});
$dispatcher->dispatchAfterCommit('order.paid');

ok($afterCommitCalled === true, 'after-commit handler called');
ok($afterDispatchCalled === true, 'after-commit event dispatched');

echo "Events smoke tests OK\n";
