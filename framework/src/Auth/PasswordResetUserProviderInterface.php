<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth;

interface PasswordResetUserProviderInterface
{
    public function updatePassword(mixed $user, string $passwordHash): void;
}
