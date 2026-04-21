<?php

declare(strict_types=1);

use Fnlla\Http\Request;
use Fnlla\Http\Response;
use Fnlla\Http\Router;
use Fnlla\Http\Stream;
use Fnlla\Http\Uri;
use Fnlla\Support\Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testParamRegexAndTrailingSlash(): void
    {
        $router = new Router();
        $router->get('/items/{id:\\d+}', static function (Request $request): Response {
            return Response::text('ok');
        });

        $req = new Request('GET', new Uri('http://localhost/items/123'));
        $res = $router->dispatch($req);
        $this->assertSame(200, $res->getStatusCode());

        $req = new Request('GET', new Uri('http://localhost/items/123/'));
        $res = $router->dispatch($req);
        $this->assertSame(200, $res->getStatusCode());

        $req = new Request('GET', new Uri('http://localhost/items/abc'));
        $res = $router->dispatch($req);
        $this->assertSame(404, $res->getStatusCode());
    }

    public function test404vs405(): void
    {
        $router = new Router();
        $router->post('/save', static fn (): Response => Response::text('ok'));

        $req = new Request('GET', new Uri('http://localhost/save'));
        $res = $router->dispatch($req);
        $this->assertSame(405, $res->getStatusCode());

        $req = new Request('GET', new Uri('http://localhost/missing'));
        $res = $router->dispatch($req);
        $this->assertSame(404, $res->getStatusCode());
    }

    public function testUrlEncoding(): void
    {
        $router = new Router();
        $router->get('/items/{name}', static fn (): Response => Response::text('ok'), 'items.show');

        $url = $router->url('items.show', ['name' => 'hello world']);
        $this->assertSame('/items/hello%20world', $url);
    }

    public function testResponseInterfaceIsNormalized(): void
    {
        $router = new Router();
        $router->get('/custom', static function (): PsrResponseInterface {
            return new class implements PsrResponseInterface {
                private int $status = 201;
                private string $reason = 'Created';
                private string $protocol = '1.0';
                private array $headers = ['X-Test' => ['yes']];
                private \Fnlla\Support\Psr\Http\Message\StreamInterface $body;

                public function __construct()
                {
                    $this->body = Stream::fromString('hello');
                }

                public function getProtocolVersion(): string
                {
                    return $this->protocol;
                }

                public function withProtocolVersion(string $version): self
                {
                    $clone = clone $this;
                    $clone->protocol = $version;
                    return $clone;
                }

                public function getHeaders(): array
                {
                    return $this->headers;
                }

                public function hasHeader(string $name): bool
                {
                    $key = strtolower($name);
                    foreach ($this->headers as $header => $_) {
                        if (strtolower($header) === $key) {
                            return true;
                        }
                    }
                    return false;
                }

                public function getHeader(string $name): array
                {
                    $key = strtolower($name);
                    foreach ($this->headers as $header => $values) {
                        if (strtolower($header) === $key) {
                            return $values;
                        }
                    }
                    return [];
                }

                public function getHeaderLine(string $name): string
                {
                    return implode(',', $this->getHeader($name));
                }

                public function withHeader(string $name, string|array $value): self
                {
                    $clone = clone $this;
                    $clone->headers[$name] = is_array($value) ? $value : [$value];
                    return $clone;
                }

                public function withAddedHeader(string $name, string|array $value): self
                {
                    $clone = clone $this;
                    $current = $clone->getHeader($name);
                    $added = is_array($value) ? $value : [$value];
                    $clone->headers[$name] = array_merge($current, $added);
                    return $clone;
                }

                public function withoutHeader(string $name): self
                {
                    $clone = clone $this;
                    foreach ($clone->headers as $header => $_) {
                        if (strcasecmp($header, $name) === 0) {
                            unset($clone->headers[$header]);
                        }
                    }
                    return $clone;
                }

                public function getBody(): \Fnlla\Support\Psr\Http\Message\StreamInterface
                {
                    return $this->body;
                }

                public function withBody(\Fnlla\Support\Psr\Http\Message\StreamInterface $body): self
                {
                    $clone = clone $this;
                    $clone->body = $body;
                    return $clone;
                }

                public function getStatusCode(): int
                {
                    return $this->status;
                }

                public function withStatus(int $code, string $reasonPhrase = ''): self
                {
                    $clone = clone $this;
                    $clone->status = $code;
                    if ($reasonPhrase !== '') {
                        $clone->reason = $reasonPhrase;
                    }
                    return $clone;
                }

                public function getReasonPhrase(): string
                {
                    return $this->reason;
                }
            };
        });

        $req = new Request('GET', new Uri('http://localhost/custom'));
        $res = $router->dispatch($req);

        $this->assertSame(201, $res->getStatusCode());
        $this->assertSame('Created', $res->getReasonPhrase());
        $this->assertSame('1.0', $res->getProtocolVersion());
        $this->assertSame('yes', $res->getHeaderLine('X-Test'));
        $this->assertSame('hello', (string) $res->getBody());
    }

    public function testAllowHeaderOn405(): void
    {
        $router = new Router();
        $router->get('/ping', static fn (): Response => Response::text('ok'));
        $router->post('/ping', static fn (): Response => Response::text('ok'));

        $req = new Request('PUT', new Uri('http://localhost/ping'));
        $res = $router->dispatch($req);

        $this->assertSame(405, $res->getStatusCode());
        $this->assertSame('GET, POST', $res->getHeaderLine('Allow'));
    }

    public function testOptionsReturnsAllowHeader(): void
    {
        $router = new Router();
        $router->get('/ping', static fn (): Response => Response::text('ok'));

        $req = new Request('OPTIONS', new Uri('http://localhost/ping'));
        $res = $router->dispatch($req);

        $this->assertSame(204, $res->getStatusCode());
        $this->assertSame('GET', $res->getHeaderLine('Allow'));
    }

    public function testMiddlewareAliasResolves(): void
    {
        $router = new Router();
        $router->middlewareAlias('mark', static function (Request $request, callable $next): Response {
            $response = $next($request);
            return $response->withHeader('X-Alias', 'yes');
        });

        $router->get('/alias', static fn (): Response => Response::text('ok'), null, ['mark']);

        $req = new Request('GET', new Uri('http://localhost/alias'));
        $res = $router->dispatch($req);

        $this->assertSame('yes', $res->getHeaderLine('X-Alias'));
    }
}

