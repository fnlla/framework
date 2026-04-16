<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Rbac;

final class Permission
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
