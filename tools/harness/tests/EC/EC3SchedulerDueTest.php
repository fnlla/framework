<?php

declare(strict_types=1);

use Finella\Scheduler\ScheduleTask;
use Finella\Testing\TestCase;
use PHPUnit\Framework\Assert;

final class EC3SchedulerDueTest extends TestCase
{
    protected bool $useDatabase = false;

    public function testSchedulerDueNow(): void
    {
        $task = new ScheduleTask('ec', fn () => null);
        $task->everyMinute();

        $now = new DateTimeImmutable('2026-02-18 10:01:00', new DateTimeZone('UTC'));
        Assert::assertTrue($task->isDue($now, null), 'Expected task to be due when never run.');

        $lastRun = $now->getTimestamp();
        Assert::assertFalse($task->isDue($now, $lastRun), 'Expected task to be not due immediately after run.');
    }
}
