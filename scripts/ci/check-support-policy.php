<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$policyPath = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'support-policy' . DIRECTORY_SEPARATOR . 'index.md';
$readmePath = $root . DIRECTORY_SEPARATOR . 'README.md';

if (!is_file($policyPath)) {
    fwrite(STDERR, "Missing support policy file: {$policyPath}\n");
    exit(1);
}

if (!is_file($readmePath)) {
    fwrite(STDERR, "Missing README file: {$readmePath}\n");
    exit(1);
}

$policy = (string) file_get_contents($policyPath);
$readme = (string) file_get_contents($readmePath);

$errors = [];

if (!preg_match('/Current supported line:\\s*(\\d+\\.\\d+\\.x)\\./i', $policy, $lineMatch)) {
    $errors[] = 'Support policy missing current supported line.';
}

if (!preg_match('/Active support until\\s+([^\\.]+)\\./i', $policy, $activeMatch)) {
    $errors[] = 'Support policy missing active support date.';
}

if (!preg_match('/Security-only support until\\s+([^\\.]+)\\./i', $policy, $securityMatch)) {
    $errors[] = 'Support policy missing security-only support date.';
}

$line = $lineMatch[1] ?? '';
$activeDate = trim((string) ($activeMatch[1] ?? ''));
$securityDate = trim((string) ($securityMatch[1] ?? ''));

if ($line === '' || $activeDate === '' || $securityDate === '') {
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    exit(1);
}

if (!preg_match(
    '/Current supported line:\\s*(\\d+\\.\\d+\\.x)\\s*\\(active support until\\s*([^,]+),\\s*security-only until\\s*([^\\)]+)\\)/i',
    $readme,
    $readmeMatch
)) {
    fwrite(STDERR, "README missing current supported line block.\n");
    exit(1);
}

$readmeLine = trim((string) ($readmeMatch[1] ?? ''));
$readmeActive = trim((string) ($readmeMatch[2] ?? ''));
$readmeSecurity = trim((string) ($readmeMatch[3] ?? ''));

if ($readmeLine !== $line) {
    $errors[] = "README supported line mismatch: expected {$line}, found {$readmeLine}.";
}
if ($readmeActive !== $activeDate) {
    $errors[] = "README active support date mismatch: expected {$activeDate}, found {$readmeActive}.";
}
if ($readmeSecurity !== $securityDate) {
    $errors[] = "README security-only support date mismatch: expected {$securityDate}, found {$readmeSecurity}.";
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    exit(1);
}

echo "Support policy OK.\n";

