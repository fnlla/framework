<?php

declare(strict_types=1);

use Fnlla\\Http\Request;
use Fnlla\\Http\Stream;
use Fnlla\\Http\Uri;
use PHPUnit\Framework\TestCase;

final class RequestParsingTest extends TestCase
{
    private array $serverBackup = [];
    private array $postBackup = [];

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->postBackup = $_POST;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_POST = $this->postBackup;
    }

    public function testMethodOverrideFromPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'PUT';

        $method = $this->callPrivateStatic(Request::class, 'detectMethod');
        $this->assertSame('PUT', $method);
    }

    public function testMethodOverrideFromHeader(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['_method']);
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PATCH';

        $method = $this->callPrivateStatic(Request::class, 'detectMethod');
        $this->assertSame('PATCH', $method);
    }

    public function testJsonBodyParsing(): void
    {
        $request = new Request(
            'POST',
            new Uri('http://localhost/test'),
            ['Content-Type' => 'application/json'],
            Stream::fromString('{"a":1,"b":"x"}')
        );

        $parsed = $this->callPrivateStatic(Request::class, 'parseBody', $request);
        $this->assertSame(['a' => 1, 'b' => 'x'], $parsed);
    }

    public function testEmptyBodyParsing(): void
    {
        $request = new Request(
            'PUT',
            new Uri('http://localhost/test'),
            ['Content-Type' => 'application/json'],
            Stream::fromString('')
        );

        $parsed = $this->callPrivateStatic(Request::class, 'parseBody', $request);
        $this->assertNull($parsed);
    }

    public function testInvalidJsonParsing(): void
    {
        $request = new Request(
            'POST',
            new Uri('http://localhost/test'),
            ['Content-Type' => 'application/json'],
            Stream::fromString('{"bad":')
        );

        $parsed = $this->callPrivateStatic(Request::class, 'parseBody', $request);
        $this->assertNull($parsed);
    }

    public function testWantsJsonAcceptHeader(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost/test'),
            ['Accept' => 'application/json']
        );

        $this->assertTrue($request->wantsJson());
    }

    public function testWantsJsonPrefersHtml(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost/test'),
            ['Accept' => 'text/html,application/json;q=0.5']
        );

        $this->assertFalse($request->wantsJson());
    }

    public function testWantsJsonPrefersJsonOverHtml(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost/test'),
            ['Accept' => 'text/html;q=0.4,application/json;q=0.9']
        );

        $this->assertTrue($request->wantsJson());
    }

    public function testWantsJsonFromAjax(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost/test'),
            ['Accept' => '*/*', 'X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertTrue($request->wantsJson());
    }

    public function testWantsJsonFromApiPath(): void
    {
        $request = new Request(
            'GET',
            new Uri('http://localhost/api/users')
        );

        $this->assertTrue($request->wantsJson());
    }

    private function callPrivateStatic(string $class, string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod($class, $method);
        $ref->setAccessible(true);
        return $ref->invoke(null, ...$args);
    }
}

