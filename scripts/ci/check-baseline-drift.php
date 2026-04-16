<?php

declare(strict_types=1);

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function truthy(string|false $value): bool
{
    if ($value === false) {
        return false;
    }

    return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
}

function lineCount(string $contents): int
{
    $trimmed = rtrim($contents, "\r\n");
    if ($trimmed === '') {
        return 0;
    }

    return substr_count($trimmed, "\n") + 1;
}

function nullDevice(): string
{
    return PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
}

function gitRefExists(string $ref): bool
{
    $cmd = 'git rev-parse --verify ' . escapeshellarg($ref) . ' 2> ' . escapeshellarg(nullDevice());
    exec($cmd, $output, $code);

    return $code === 0;
}

function gitShow(string $ref, string $path): ?string
{
    $spec = $ref . ':' . $path;
    $cmd = 'git show ' . escapeshellarg($spec) . ' 2> ' . escapeshellarg(nullDevice());
    exec($cmd, $output, $code);
    if ($code !== 0) {
        return null;
    }

    return implode("\n", $output);
}

$options = getopt('', ['ref::']);

if (truthy(getenv('BASELINE_ALLOW_GROWTH'))) {
    echo "Baseline drift check: growth allowed (BASELINE_ALLOW_GROWTH=1).\n";
    exit(0);
}

$ref = (string) ($options['ref'] ?? '');
if ($ref === '') {
    $baseRef = getenv('GITHUB_BASE_REF');
    if (is_string($baseRef) && $baseRef !== '') {
        $ref = 'origin/' . $baseRef;
    } else {
        $ref = 'HEAD~1';
    }
}

if (!gitRefExists($ref)) {
    echo "Baseline drift check skipped (ref {$ref} not found).\n";
    exit(0);
}

$targets = [
    'tools/phpstan.enterprise-baseline.neon' => 'PHPStan enterprise baseline',
    'tools/psalm-baseline.xml' => 'Psalm baseline',
];

$failures = [];

foreach ($targets as $path => $label) {
    if (!is_file($path)) {
        continue;
    }

    $baseContents = gitShow($ref, $path);
    if ($baseContents === null) {
        continue;
    }

    $currentContents = file_get_contents($path);
    if ($currentContents === false) {
        fail("Unable to read {$path}.");
    }

    $currentLines = lineCount($currentContents);
    $baseLines = lineCount($baseContents);

    if ($baseLines === 0) {
        continue;
    }

    if ($currentLines > $baseLines) {
        $delta = $currentLines - $baseLines;
        $failures[] = sprintf(
            '%s grew by %d lines (%d -> %d).',
            $label,
            $delta,
            $baseLines,
            $currentLines
        );
    }
}

if ($failures !== []) {
    fail(
        "Baseline drift check failed:\n- " .
        implode("\n- ", $failures) .
        "\nReduce baselines or set BASELINE_ALLOW_GROWTH=1 to override."
    );
}

echo "Baseline drift check OK.\n";
