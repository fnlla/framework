<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Queue;

use Finella\Core\Container;

interface JobInterface
{
    public function handle(Container $app): void;
}