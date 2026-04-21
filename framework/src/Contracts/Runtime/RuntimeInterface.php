<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Contracts\Runtime;

use Finella\Contracts\Http\KernelInterface;

/**
 * @api
 */
interface RuntimeInterface
{
    public function run(KernelInterface $kernel): void;
}






