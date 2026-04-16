<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Factory;

use Finella\Support\Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFactoryInterface
{
    /**
     * @param mixed $uri
     */
    public function createServerRequest(string $method, mixed $uri, array $serverParams = []): ServerRequestInterface;
}






