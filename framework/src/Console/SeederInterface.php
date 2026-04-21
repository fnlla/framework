<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Console;

use PDO;

interface SeederInterface
{
    public function run(PDO $pdo): void;
}
