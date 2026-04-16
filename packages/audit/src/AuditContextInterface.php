<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Audit;

interface AuditContextInterface
{
    public function userId(): int|string|null;

    public function ipAddress(): ?string;

    public function userAgent(): ?string;
}




