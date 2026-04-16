<?php

declare(strict_types=1);

use Finella\Support\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['FINELLA_ENV_TEST'], $_SERVER['FINELLA_ENV_TEST']);
        putenv('FINELLA_ENV_TEST');
    }

    public function testReturnsDefaultWhenMissing(): void
    {
        $this->assertSame('fallback', Env::get('FINELLA_ENV_TEST', 'fallback'));
    }

    public function testParsesEnvValues(): void
    {
        putenv('FINELLA_ENV_TEST=true');
        $this->assertTrue(Env::get('FINELLA_ENV_TEST'));

        putenv('FINELLA_ENV_TEST=(false)');
        $this->assertFalse(Env::get('FINELLA_ENV_TEST'));

        putenv('FINELLA_ENV_TEST=(null)');
        $this->assertNull(Env::get('FINELLA_ENV_TEST'));

        putenv('FINELLA_ENV_TEST=(empty)');
        $this->assertSame('', Env::get('FINELLA_ENV_TEST'));
    }

    public function testReadsFromGetenv(): void
    {
        putenv('FINELLA_ENV_TEST=Hello');
        $this->assertSame('Hello', Env::get('FINELLA_ENV_TEST'));
    }
}
