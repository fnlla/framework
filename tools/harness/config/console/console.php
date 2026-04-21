<?php

declare(strict_types=1);

$commands = [
    \Fnlla\\Deploy\Commands\DeployHealthCommand::class,
    \Fnlla\\Deploy\Commands\DeployWarmupCommand::class,
    \Fnlla\\Docs\Commands\DocsGenerateCommand::class,
    \Fnlla\\Docs\Commands\DocsSyncCommand::class,
    \Fnlla\\MailPreview\Commands\MailPreviewPublishCommand::class,
];

$commands = array_values(array_filter($commands, static fn (string $command): bool => class_exists($command)));

return [
    // Register custom CLI commands here:
    // App\Commands\ExampleCommand::class,
    'commands' => $commands,
];
