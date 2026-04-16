<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Mail;

final class Address
{
    public function __construct(
        public string $email,
        public ?string $name = null
    ) {
    }
}