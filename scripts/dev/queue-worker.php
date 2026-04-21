<?php
declare(strict_types=1);

use Fnlla\Core\Application;
use Fnlla\Queue\DatabaseQueue;
use Fnlla\Queue\QueueManager;
use Fnlla\Queue\QueueWorker;
use Fnlla\Queue\SyncQueue;

$root = dirname(__DIR__, 2);
$appRoot = $root . '/tools/harness';
$appRoot = is_dir($appRoot) ? $appRoot : $root;
$autoloadCandidates = [
    $root . '/vendor/autoload.php',
    $root . '/tools/harness/vendor/autoload.php',
];

$autoload = null;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Missing composer autoload. Run composer install.\n");
    exit(1);
}

require $autoload;

if (getenv('APP_ROOT') === false) {
    putenv('APP_ROOT=' . $appRoot);
    $_ENV['APP_ROOT'] = $appRoot;
    $_SERVER['APP_ROOT'] = $appRoot;
}

$bootstrap = $appRoot . '/bootstrap/app.php';
if (is_file($bootstrap)) {
    require $bootstrap;
}

$app = $GLOBALS['Fnlla_app'] ?? null;
if (!$app instanceof Application) {
    fwrite(STDERR, "Unable to bootstrap Fnlla app.\n");
    exit(1);
}

if (!class_exists(QueueManager::class)) {
    fwrite(STDERR, "QueueManager not available. Install fnlla/queue.\n");
    exit(1);
}

$queueManager = $app->has(QueueManager::class)
    ? $app->make(QueueManager::class)
    : new QueueManager((array) $app->config()->get('queue', []), fn () => $app);

if (!$queueManager instanceof QueueManager) {
    fwrite(STDERR, "Unable to resolve QueueManager.\n");
    exit(1);
}

$queue = $queueManager->queue();
if ($queue instanceof SyncQueue) {
    echo "Queue driver is sync; jobs run immediately. Nothing to work.\n";
    exit(0);
}

if (!$queue instanceof DatabaseQueue) {
    fwrite(STDERR, "Queue worker requires database driver.\n");
    exit(1);
}

$args = $_SERVER['argv'] ?? [];
array_shift($args);

$maxJobs = 0;
$sleepSeconds = 1;
$maxAttempts = null;
$retryAfter = null;
$backoff = [];

foreach ($args as $arg) {
    if ($arg === '--once') {
        $maxJobs = 1;
        continue;
    }
    if (str_starts_with($arg, '--limit=')) {
        $maxJobs = max(0, (int) substr($arg, 8));
        continue;
    }
    if (str_starts_with($arg, '--sleep=')) {
        $sleepSeconds = max(0, (int) substr($arg, 8));
        continue;
    }
    if (str_starts_with($arg, '--max-attempts=')) {
        $maxAttempts = max(1, (int) substr($arg, 15));
        continue;
    }
    if (str_starts_with($arg, '--retry-after=')) {
        $retryAfter = max(1, (int) substr($arg, 14));
        continue;
    }
    if (str_starts_with($arg, '--backoff=')) {
        $raw = trim(substr($arg, 10));
        if ($raw !== '') {
            $backoff = array_map('intval', array_filter(array_map('trim', explode(',', $raw)), static fn ($v) => $v !== ''));
        }
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        echo "Usage: php scripts/dev/queue-worker.php [--once] [--limit=N] [--sleep=N] [--max-attempts=N] [--retry-after=N] [--backoff=5,10]\n";
        exit(0);
    }
}

$worker = new QueueWorker($queue, $app, $maxAttempts, $backoff, $retryAfter);
$processed = $worker->work($maxJobs, $sleepSeconds);

echo "Processed {$processed} jobs.\n";
