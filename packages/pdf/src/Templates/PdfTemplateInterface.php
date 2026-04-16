<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Pdf\Templates;

interface PdfTemplateInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function render(array $data = []): string;
}


