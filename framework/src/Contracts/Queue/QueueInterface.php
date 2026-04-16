<?php

/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Contracts\Queue;

/**
 * @api
 */
interface QueueInterface
{
    public function push(callable|string $job, array $payload = []): mixed;
}




