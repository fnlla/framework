<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth;

interface RegistrationUserProviderInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): mixed;
}
