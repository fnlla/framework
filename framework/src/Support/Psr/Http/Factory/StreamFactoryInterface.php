<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Factory;

use Finella\Support\Psr\Http\Message\StreamInterface;

interface StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface;

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface;

    /**
     * @param resource $resource
     */
    public function createStreamFromResource(mixed $resource): StreamInterface;
}






