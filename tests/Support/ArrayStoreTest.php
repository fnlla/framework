<?php

declare(strict_types=1);

use Fnlla\\Support\ArrayStore;
use PHPUnit\Framework\TestCase;

final class ArrayStoreTest extends TestCase
{
    public function testPutGetForgetAndClear(): void
    {
        $store = new ArrayStore();

        $this->assertNull($store->get('missing'));

        $store->put('foo', 'bar');
        $this->assertSame('bar', $store->get('foo'));

        $store->forget('foo');
        $this->assertNull($store->get('foo'));

        $store->put('foo', 'bar');
        $store->clear();
        $this->assertNull($store->get('foo'));
    }
}
