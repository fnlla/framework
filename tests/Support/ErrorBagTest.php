<?php

declare(strict_types=1);

use Fnlla\Support\ErrorBag;
use PHPUnit\Framework\TestCase;

final class ErrorBagTest extends TestCase
{
    public function testHasAndFirst(): void
    {
        $bag = new ErrorBag([
            'email' => ['Required', 'Invalid'],
            'name' => 'Missing',
        ]);

        $this->assertTrue($bag->has('email'));
        $this->assertSame('Required', $bag->first('email'));
        $this->assertSame('Missing', $bag->first('name'));
        $this->assertSame('fallback', $bag->first('missing', 'fallback'));
    }

    public function testGetAllAndCount(): void
    {
        $bag = new ErrorBag([
            'email' => ['Required', 'Invalid'],
            'name' => 'Missing',
        ]);

        $this->assertSame(['Required', 'Invalid'], $bag->get('email'));
        $this->assertSame(['Missing'], $bag->get('name'));
        $this->assertSame(['Required', 'Invalid', 'Missing'], $bag->all());
        $this->assertSame(3, $bag->count());
    }

    public function testIteratorReturnsBag(): void
    {
        $bag = new ErrorBag([
            'email' => ['Required'],
        ], 'signup');

        $this->assertSame('signup', $bag->bag());

        $items = [];
        foreach ($bag as $key => $value) {
            $items[$key] = $value;
        }

        $this->assertSame(['email' => ['Required']], $items);
    }
}
