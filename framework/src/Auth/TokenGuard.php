<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth;

use Finella\Http\Request;

final class TokenGuard
{
    public function __construct(
        private UserProviderInterface $provider,
        private string $header = 'Authorization',
        private string $prefix = 'Bearer'
    ) {
    }

    public function user(Request $request): mixed
    {
        $token = $this->extractToken($request);
        if ($token === '') {
            return null;
        }
        return $this->provider->retrieveByToken($token);
    }

    public function check(Request $request): bool
    {
        return $this->user($request) !== null;
    }

    private function extractToken(Request $request): string
    {
        $header = $request->getHeaderLine($this->header);
        if ($header === '') {
            return '';
        }
        if (stripos($header, $this->prefix . ' ') === 0) {
            return trim(substr($header, strlen($this->prefix)));
        }
        return '';
    }
}
