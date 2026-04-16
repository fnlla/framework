<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Auth;

interface UserProviderInterface
{
    public function retrieveById(string|int $id): mixed;

    public function retrieveByToken(string $token): mixed;
}
