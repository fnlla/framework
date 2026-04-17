<?php

declare(strict_types=1);

if (!is_dir(__DIR__ . '/../.git')) {
    fwrite(STDERR, "This command must be run from the framework repository.\n");
    exit(1);
}

$command = 'git config core.hooksPath .githooks';
$output = [];
$code = 0;
exec($command, $output, $code);

if ($code !== 0) {
    fwrite(STDERR, "Failed to configure core.hooksPath.\n");
    exit($code);
}

fwrite(STDOUT, "Git hooks installed. Active hooks path: .githooks\n");
