<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Contracts\Events;

/**
 * @api
 */
interface EventDispatcherInterface
{
    public function listen(string $event, callable|string $listener): void;

    public function dispatch(object|string $event, array $payload = []): array;

    public function dispatchAfterCommit(object|string $event, array $payload = []): void;
}




