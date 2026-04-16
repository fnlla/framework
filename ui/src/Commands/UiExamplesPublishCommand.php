<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */

declare(strict_types=1);

namespace Finella\Ui\Commands;

use Finella\Console\CommandInterface;
use Finella\Console\ConsoleIO;

final class UiExamplesPublishCommand implements CommandInterface
{
    private UiElementsPublishCommand $delegate;

    public function __construct()
    {
        $this->delegate = new UiElementsPublishCommand();
    }

    public function getName(): string
    {
        return 'ui:examples:publish';
    }

    public function getDescription(): string
    {
        return 'Deprecated alias for ui:elements:publish.';
    }

    public function run(array $args, array $options, ConsoleIO $io, string $root): int
    {
        return $this->delegate->run($args, $options, $io, $root);
    }
}
