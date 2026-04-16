<?php

declare(strict_types=1);

use Finella\Mail\Mail;
use Finella\Queue\DatabaseQueue;
use Finella\Queue\QueueManager;
use Finella\Queue\QueueWorker;
use Finella\Scheduler\Schedule;
use Finella\Testing\TestCase;
use Finella\RequestLogging\RequestLoggerMiddleware;

final class EC3QueueMailSchedulerTest extends TestCase
{
    public function setUp(): void
    {
        $this->csrfEnabled = true;
        parent::setUp();
    }

    public function testQueueMailAndSchedulerAndObservability(): void
    {
        $this->withCsrf();

        $this->app()->config()->set('queue', [
            'driver' => 'database',
            'database' => [
                'table' => 'queue_jobs',
                'failed_table' => 'queue_failed_jobs',
                'max_attempts' => 3,
                'retry_after' => 60,
                'backoff' => '',
            ],
        ]);

        $manager = new QueueManager($this->app()->config()->get('queue', []), fn () => $this->app());
        $this->app()->instance(QueueManager::class, $manager);

        $http = $this->app()->config()->get('http', []);
        if (!is_array($http)) {
            $http = [];
        }
        $global = $http['global'] ?? [];
        if (!is_array($global)) {
            $global = [];
        }
        $global[] = RequestLoggerMiddleware::class;
        $http['global'] = array_values(array_unique($global));
        $this->app()->config()->set('http', $http);

        Mail::fake();

        $this->post('/_ec/welcome', [], [
            'Referer' => '/_ec/register',
        ])->assertStatus(200);

        $queue = $manager->queue();
        if (!$queue instanceof DatabaseQueue) {
            throw new RuntimeException('Database queue driver expected for EC3.');
        }

        $worker = new QueueWorker($queue, $this->app());
        $worker->work(1, 0);

        Mail::assertSent(fn ($message) => $message->subject === 'Welcome');

        $response = $this->get('/_ec/obs', ['X-Request-Id' => 'ec-request-id']);
        $response->assertStatus(200);
        $header = $response->response()->getHeaderLine('X-Request-Id');
        if ($header !== 'ec-request-id') {
            throw new RuntimeException('Expected X-Request-Id header.');
        }

        $root = defined('APP_ROOT') ? APP_ROOT : getcwd();
        $cachePath = rtrim((string) $root, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'ec-schedule.json';
        $registry = new Schedule($this->app(), $cachePath, 'UTC');

        $scheduleFile = rtrim((string) $root, '/\\') . DIRECTORY_SEPARATOR . 'schedule.php';
        if (!is_file($scheduleFile)) {
            throw new RuntimeException('schedule.php missing for EC3.');
        }
        $loaded = require $scheduleFile;
        if (is_callable($loaded)) {
            $loaded($registry);
        }

        $registry->runDue();

        $marker = rtrim((string) $root, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'ec-scheduled.txt';
        if (!is_file($marker)) {
            throw new RuntimeException('Scheduler marker was not created.');
        }
    }
}
