<?php

declare(strict_types=1);

$commands = [
    \Finella\Deploy\Commands\DeployHealthCommand::class,
    \Finella\Deploy\Commands\DeployWarmupCommand::class,
    \Finella\Ui\Commands\UiPublishCommand::class,
    \Finella\Ui\Commands\UiAdminPublishCommand::class,
    \Finella\Docs\Commands\DocsGenerateCommand::class,
    \Finella\Docs\Commands\DocsSyncCommand::class,
    \Finella\MailPreview\Commands\MailPreviewPublishCommand::class,
];

$commands = array_values(array_filter($commands, static fn (string $command): bool => class_exists($command)));

return [
    // Register custom CLI commands here:
    // App\Commands\ExampleCommand::class,
    'commands' => $commands,
];
