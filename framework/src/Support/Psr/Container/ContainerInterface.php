<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Container;

interface ContainerInterface
{
    public function get(string $id): mixed;

    public function has(string $id): bool;
}




