<?php

declare(strict_types=1);

use Fnlla\\Core\ConfigRepository;
use Fnlla\\Core\Application;
use Fnlla\\Core\ServiceProvider;
use Fnlla\\Http\HttpKernel;
use Fnlla\\Http\Request;
use Fnlla\\Http\Uri;
use Fnlla\\Runtime\Resetter;
use PHPUnit\Framework\TestCase;

final class WarmKernelTest extends TestCase
{
    public function testWarmKernelBootsOnceAndResetsPerRequest(): void
    {
        WarmKernelProvider::$bootCount = 0;
        $resetter = new CountingResetter();

        $config = new ConfigRepository([
            'env' => 'test',
            'debug' => false,
            'http' => [
                'request_id_header' => true,
                'trace_id_header' => true,
                'span_id_header' => true,
            ],
            'providers' => [
                WarmKernelProvider::class,
            ],
        ]);

        $app = new Application(__DIR__, $config);
        $app->registerResetter($resetter);

        $kernel = new HttpKernel($app, true);
        $kernel->boot();

        $request = new Request('GET', new Uri('http://localhost/'));
        $responseOne = $kernel->handle($request);
        $responseTwo = $kernel->handle($request);

        $this->assertTrue($kernel->isBooted());
        $this->assertSame(1, WarmKernelProvider::$bootCount);
        $this->assertSame(2, $resetter->count);

        $this->assertNotSame('', $responseOne->getHeaderLine('X-Request-Id'));
        $this->assertNotSame('', $responseOne->getHeaderLine('X-Trace-Id'));
        $this->assertNotSame('', $responseOne->getHeaderLine('X-Span-Id'));

        $this->assertNotSame('', $responseTwo->getHeaderLine('X-Request-Id'));
        $this->assertNotSame('', $responseTwo->getHeaderLine('X-Trace-Id'));
        $this->assertNotSame('', $responseTwo->getHeaderLine('X-Span-Id'));
    }
}

final class WarmKernelProvider extends ServiceProvider
{
    public static int $bootCount = 0;

    public function boot(): void
    {
        self::$bootCount++;
    }
}

final class CountingResetter implements Resetter
{
    public int $count = 0;

    public function reset(): void
    {
        $this->count++;
    }
}
