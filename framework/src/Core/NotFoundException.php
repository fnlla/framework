<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Core;

use Finella\Support\Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Container entry not found.
 *
 * @api
 */
final class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}






