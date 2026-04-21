<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth;

interface CredentialsUserProviderInterface
{
    /**
     * @param array<string, mixed> $credentials
     */
    public function retrieveByCredentials(array $credentials): mixed;

    /**
     * @param array<string, mixed> $credentials
     */
    public function validateCredentials(mixed $user, array $credentials): bool;
}
