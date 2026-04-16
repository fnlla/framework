<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Factory;

use Finella\Support\Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface;
}






