<?php

declare(strict_types=1);

use Fnlla\Scheduler\Schedule;

return function (Schedule $schedule): void {
    // Example task kept intentionally lightweight for local validation.
    $schedule->call('touch-schedule', function (): void {
        $path = __DIR__ . '/storage/schedule.log';
        if (!is_dir(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        @file_put_contents($path, 'ok');
    })->everyMinute();
};
