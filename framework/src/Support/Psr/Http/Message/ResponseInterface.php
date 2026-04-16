<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Message;

interface ResponseInterface extends MessageInterface
{
    public function getStatusCode(): int;

    public function withStatus(int $code, string $reasonPhrase = ''): self;

    public function getReasonPhrase(): string;
}




