<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Rbac;

final class Role
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
