<?php

declare(strict_types=1);

use Fnlla\\Support\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['Fnlla_ENV_TEST'], $_SERVER['Fnlla_ENV_TEST']);
        putenv('Fnlla_ENV_TEST');
    }

    public function testReturnsDefaultWhenMissing(): void
    {
        $this->assertSame('fallback', Env::get('Fnlla_ENV_TEST', 'fallback'));
    }

    public function testParsesEnvValues(): void
    {
        putenv('Fnlla_ENV_TEST=true');
        $this->assertTrue(Env::get('Fnlla_ENV_TEST'));

        putenv('Fnlla_ENV_TEST=(false)');
        $this->assertFalse(Env::get('Fnlla_ENV_TEST'));

        putenv('Fnlla_ENV_TEST=(null)');
        $this->assertNull(Env::get('Fnlla_ENV_TEST'));

        putenv('Fnlla_ENV_TEST=(empty)');
        $this->assertSame('', Env::get('Fnlla_ENV_TEST'));
    }

    public function testReadsFromGetenv(): void
    {
        putenv('Fnlla_ENV_TEST=Hello');
        $this->assertSame('Hello', Env::get('Fnlla_ENV_TEST'));
    }
}
