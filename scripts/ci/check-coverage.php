<?php

declare(strict_types=1);

function fail(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

function readOption(array $options, string $name, ?string $envName = null, ?string $default = null): string
{
    if (isset($options[$name]) && $options[$name] !== false) {
        return (string) $options[$name];
    }

    if ($envName !== null) {
        $envValue = getenv($envName);
        if ($envValue !== false && $envValue !== '') {
            return (string) $envValue;
        }
    }

    return $default ?? '';
}

$options = getopt('', ['path::', 'min-lines::']);
$path = readOption($options, 'path', 'COVERAGE_PATH', '.artifacts/coverage.xml');
$minLinesRaw = readOption($options, 'min-lines', 'COVERAGE_MIN_LINES', '25');
$minLines = (float) $minLinesRaw;

if ($path === '') {
    fail('Coverage path is required.');
}

if (!is_file($path)) {
    fail("Coverage report not found at {$path}.");
}

libxml_use_internal_errors(true);
$xml = simplexml_load_file($path);
if ($xml === false) {
    fail('Unable to parse coverage XML.');
}

$metricsNode = null;
if (isset($xml->project->metrics)) {
    $metricsNode = $xml->project->metrics;
} else {
    $nodes = $xml->xpath('//metrics');
    if (is_array($nodes) && $nodes !== []) {
        $metricsNode = $nodes[count($nodes) - 1];
    }
}

if ($metricsNode === null) {
    fail('Coverage metrics not found in report.');
}

$statements = (int) ($metricsNode['statements'] ?? 0);
$coveredStatements = (int) ($metricsNode['coveredstatements'] ?? 0);

if ($statements <= 0) {
    fail('Coverage report contains no statements.');
}

$coverage = ($coveredStatements / $statements) * 100;
printf("Line coverage: %.2f%% (%d/%d statements)\n", $coverage, $coveredStatements, $statements);

if ($coverage + 0.0001 < $minLines) {
    fail(sprintf('Line coverage %.2f%% is below the minimum %.2f%%.', $coverage, $minLines));
}

echo "Coverage gate OK (min {$minLines}%).\n";
