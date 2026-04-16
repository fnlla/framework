<?php

declare(strict_types=1);

use Finella\Support\CacheItem;
use PHPUnit\Framework\TestCase;

final class CacheItemTest extends TestCase
{
    public function testDefaultsAndSetters(): void
    {
        $item = new CacheItem('key');

        $this->assertSame('key', $item->getKey());
        $this->assertFalse($item->isHit());
        $this->assertSame(0, $item->expirationTimestamp());

        $item->set('value')->markHit(true);
        $this->assertSame('value', $item->get());
        $this->assertTrue($item->isHit());
    }

    public function testExpiresAfterAndAt(): void
    {
        $item = new CacheItem('key');

        $item->expiresAfter(60);
        $this->assertGreaterThan(0, $item->expirationTimestamp());

        $item->expiresAfter(new \DateInterval('PT30S'));
        $this->assertGreaterThan(0, $item->expirationTimestamp());

        $item->expiresAt(new \DateTimeImmutable('+10 seconds'));
        $this->assertGreaterThan(0, $item->expirationTimestamp());

        $item->expiresAfter(null);
        $this->assertSame(0, $item->expirationTimestamp());
    }
}
