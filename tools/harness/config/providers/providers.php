<?php

declare(strict_types=1);

return [
    'auto_discovery' => true,
    'disabled' => [
        \Finella\Debugbar\DebugbarServiceProvider::class => ['prod' => true],
        // ProviderClass::class => ['prod' => true],
        // ProviderClass::class => ['all' => true],
    ],
    'manual' => [
        \Finella\Support\EventsServiceProvider::class,
    ],
];
