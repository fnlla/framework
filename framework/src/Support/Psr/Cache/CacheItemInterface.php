<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Cache;

interface CacheItemInterface
{
    public function getKey(): string;

    public function get(): mixed;

    public function isHit(): bool;

    public function set(mixed $value): self;

    public function expiresAt(?\DateTimeInterface $expiration): self;

    public function expiresAfter(int|\DateInterval|null $time): self;
}




