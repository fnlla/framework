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
interface JobInterface
{
    public function handle(array $payload = []): mixed;
}




