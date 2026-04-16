<?php

declare(strict_types=1);

use Finella\Core\Container;
use Finella\Database\ConnectionManager;
use Finella\Queue\DatabaseQueue;
use Finella\Queue\JobInterface;
use Finella\Queue\QueueWorker;
use PHPUnit\Framework\TestCase;

final class DatabaseQueueNoopJob implements JobInterface
{
    public function handle(Container $app): void
    {
    }
}

final class DatabaseQueueFailingJob implements JobInterface
{
    public function handle(Container $app): void
    {
        throw new RuntimeException('boom');
    }
}

final class DatabaseQueueTest extends TestCase
{
    public function testPopLocksAndRelease(): void
    {
        $now = 1700000000;
        $clock = static fn (): int => $now;
        $manager = new ConnectionManager(['driver' => 'sqlite', 'path' => ':memory:']);
        $queue = new DatabaseQueue($manager, 'queue_jobs', 'queue_failed_jobs', 3, 60, 'secret', ['*'], $clock);

        $queue->dispatch(new DatabaseQueueNoopJob());

        $first = $queue->pop();
        $this->assertNotNull($first);

        $second = $queue->pop();
        $this->assertNull($second, 'job should be reserved/locked after pop');

        $queue->release($first->id(), 0);
        $third = $queue->pop();
        $this->assertNotNull($third);
    }

    public function testRetryAndMaxAttempts(): void
    {
        $now = 1700000000;
        $clock = static fn (): int => $now;
        $manager = new ConnectionManager(['driver' => 'sqlite', 'path' => ':memory:']);
        $queue = new DatabaseQueue($manager, 'queue_jobs', 'queue_failed_jobs', 2, 1, 'secret', ['*'], $clock);
        $container = new Container();

        $queue->dispatch(new DatabaseQueueFailingJob());

        $worker = new QueueWorker($queue, $container, 2, [0], 1);
        $worker->work(1, 0);
        $worker->work(1, 0);

        $pdo = $manager->connection();
        $failed = (int) $pdo->query('SELECT COUNT(*) FROM queue_failed_jobs')->fetchColumn();
        $remaining = (int) $pdo->query('SELECT COUNT(*) FROM queue_jobs')->fetchColumn();

        $this->assertSame(1, $failed);
        $this->assertSame(0, $remaining);
    }
}
