<?php

declare(strict_types=1);

use Fnlla\\Scheduler\Schedule;

return function (Schedule $schedule): void {
    // $schedule->call('prune-cache', fn () => ...)->hourly();
    // $schedule->command('reports:daily')->dailyAt('02:00');

    if (strtolower((string) getenv('APP_ENV')) === 'test') {
        $schedule->call('ec-scheduled-mark', function (): void {
            $root = defined('APP_ROOT') ? APP_ROOT : getcwd();
            $path = rtrim((string) $root, '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'ec-scheduled.txt';
            if (!is_dir(dirname($path))) {
                @mkdir(dirname($path), 0755, true);
            }
            @file_put_contents($path, 'ok');
        })->everyMinute();
    }
};
