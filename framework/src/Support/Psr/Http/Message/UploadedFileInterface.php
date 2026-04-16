<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
declare(strict_types=1);

namespace Finella\Support\Psr\Http\Message;

interface UploadedFileInterface
{
    public function getStream(): StreamInterface;

    public function moveTo(string $targetPath): void;

    public function getSize(): ?int;

    public function getError(): int;

    public function getClientFilename(): ?string;

    public function getClientMediaType(): ?string;
}




