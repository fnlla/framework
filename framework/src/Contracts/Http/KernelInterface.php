<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Contracts\Http;

use Finella\Http\Request;
use Finella\Support\Psr\Http\Message\ResponseInterface;

/**
 * @api
 */
interface KernelInterface
{
    public function boot(?string $appRoot = null): void;

    public function handle(Request $request): ResponseInterface;
}






