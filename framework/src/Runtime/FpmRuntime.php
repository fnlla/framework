<?php

/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Runtime;

use Finella\Contracts\Http\KernelInterface;
use Finella\Contracts\Runtime\RuntimeInterface;
use Finella\Http\Request;
use Finella\Support\Psr\Http\Message\ResponseInterface;

final class FpmRuntime implements RuntimeInterface
{
    public function run(KernelInterface $kernel): void
    {
        $kernel->boot();
        $request = Request::fromGlobals();
        $response = $kernel->handle($request);
        $this->emit($response);
    }

    private function emit(ResponseInterface $response): void
    {
        if (!headers_sent()) {
            http_response_code($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header($name . ': ' . $value, false);
                }
            }
        }

        echo (string) $response->getBody();
    }
}





