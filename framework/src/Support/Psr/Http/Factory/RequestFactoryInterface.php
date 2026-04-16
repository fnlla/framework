<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Factory;

use Finella\Support\Psr\Http\Message\RequestInterface;

interface RequestFactoryInterface
{
    /**
     * @param mixed $uri
     */
    public function createRequest(string $method, mixed $uri): RequestInterface;
}






