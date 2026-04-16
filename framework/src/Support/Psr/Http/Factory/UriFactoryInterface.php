<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Factory;

use Finella\Support\Psr\Http\Message\UriInterface;

interface UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface;
}






