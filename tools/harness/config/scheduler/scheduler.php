<?php

declare(strict_types=1);

return [
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'cache_path' => env('SCHEDULE_CACHE', 'storage/cache/schedule.json'),
];
