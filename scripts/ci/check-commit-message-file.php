<?php

declare(strict_types=1);

$args = $_SERVER['argv'] ?? [];
array_shift($args);

$messageFile = $args[0] ?? '';
if (!is_string($messageFile) || trim($messageFile) === '') {
    fwrite(STDERR, "Usage: php scripts/ci/check-commit-message-file.php <commit-message-file>\n");
    exit(1);
}

if (!is_file($messageFile)) {
    fwrite(STDERR, "Commit message file not found: {$messageFile}\n");
    exit(1);
}

$raw = file_get_contents($messageFile);
if (!is_string($raw)) {
    fwrite(STDERR, "Unable to read commit message file.\n");
    exit(1);
}

$subject = '';
$lines = preg_split('/\R/', $raw) ?: [];
foreach ($lines as $line) {
    $trimmed = trim((string) $line);
    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
        continue;
    }

    $subject = $trimmed;
    break;
}

if ($subject === '') {
    // Empty message is handled by git itself.
    exit(0);
}

if (preg_match('/^\s*chore(?:\([^)]+\))?:\s+/i', $subject) === 1) {
    fwrite(STDERR, "ERROR: commit subject must not start with 'chore:'.\n");
    fwrite(STDERR, "Use a plain subject without the conventional prefix.\n");
    fwrite(STDERR, "Received: {$subject}\n");
    exit(1);
}

exit(0);
