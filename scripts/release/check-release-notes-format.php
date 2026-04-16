<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$changelogPath = $root . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
$versionFilter = null;

$args = $_SERVER['argv'] ?? [];
array_shift($args);

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--changelog' && isset($args[$i + 1])) {
        $changelogPath = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--version' && isset($args[$i + 1])) {
        $versionFilter = ltrim(trim((string) $args[++$i]), 'vV');
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
}

if (!is_file($changelogPath)) {
    fail("Changelog file not found: {$changelogPath}");
}

$raw = file_get_contents($changelogPath);
if (!is_string($raw) || $raw === '') {
    fail("Unable to read changelog: {$changelogPath}");
}

$content = normalizeText($raw);
$sections = parseReleaseSections($content);

if ($sections === []) {
    fail("No release sections found in {$changelogPath}");
}

$allowed = ['HIGHLIGHTS', 'ADDED', 'CHANGED', 'DEPRECATED', 'REMOVED', 'FIXED', 'SECURITY'];
$allowedMap = [];
foreach ($allowed as $idx => $name) {
    $allowedMap[$name] = $idx;
}

$errors = [];
$validated = 0;
$latestReleaseVersion = null;

foreach ($sections as $section) {
    $version = $section['version'];
    if (strtolower($version) === 'unreleased') {
        continue;
    }
    if ($latestReleaseVersion === null) {
        $latestReleaseVersion = $version;
    }
    if ($versionFilter !== null && $version !== $versionFilter) {
        continue;
    }

    $headers = extractSectionHeaders($section['body']);
    if ($headers === []) {
        $errors[] = "Release [{$version}] has no subsection headers (expected ### Added/Changed/etc.).";
        continue;
    }

    $previousIndex = -1;
    $hasHighlights = false;
    foreach ($headers as $header) {
        $normalized = strtoupper(trim($header));
        if (!isset($allowedMap[$normalized])) {
            $errors[] = "Release [{$version}] has unsupported section header: {$header}.";
            continue;
        }

        $index = $allowedMap[$normalized];
        if ($index < $previousIndex) {
            $errors[] = "Release [{$version}] section order is invalid near {$header}.";
        }
        $previousIndex = $index;

        if ($normalized === 'HIGHLIGHTS') {
            $hasHighlights = true;
        }
    }

    $requiresHighlights = ($latestReleaseVersion !== null && $version === $latestReleaseVersion);
    if ($requiresHighlights && $headers !== [] && strtoupper(trim($headers[0])) !== 'HIGHLIGHTS') {
        $errors[] = "Latest release [{$version}] must start with ### Highlights.";
    }
    if ($requiresHighlights && !$hasHighlights) {
        $errors[] = "Latest release [{$version}] is missing ### Highlights.";
    }

    $validated++;
}

if ($versionFilter !== null && $validated === 0) {
    fail("Version {$versionFilter} not found in {$changelogPath}");
}

if ($errors !== []) {
    fail("Release notes format check failed:\n- " . implode("\n- ", $errors));
}

echo "Release notes format check OK";
if ($validated > 0) {
    echo " ({$validated} release section(s) validated).";
}
echo PHP_EOL;

function printUsage(): void
{
    echo <<<TXT
Usage:
  php scripts/release/check-release-notes-format.php [options]

Options:
  --changelog <path>   Changelog path (default: CHANGELOG.md)
  --version <x.y.z>    Validate only one release section
  --help, -h           Show this help

TXT;
}

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function normalizeText(string $content): string
{
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $content = substr($content, 3);
    }

    return str_replace(["\r\n", "\r"], "\n", $content);
}

/**
 * @return list<array{version:string,body:string}>
 */
function parseReleaseSections(string $changelog): array
{
    $sections = [];
    $pattern = '/^## \[(?P<version>[^\]]+)\][^\n]*\n(?P<body>[\s\S]*?)(?=^## \[|\z)/m';
    if (preg_match_all($pattern, $changelog, $matches, PREG_SET_ORDER) !== false) {
        foreach ($matches as $match) {
            $version = trim((string) ($match['version'] ?? ''));
            if ($version === '') {
                continue;
            }
            $sections[] = [
                'version' => ltrim($version, 'vV'),
                'body' => trim((string) ($match['body'] ?? '')),
            ];
        }
    }

    return $sections;
}

/**
 * @return list<string>
 */
function extractSectionHeaders(string $body): array
{
    $headers = [];
    $lines = explode("\n", $body);
    foreach ($lines as $line) {
        if (preg_match('/^###\s+(.+?)\s*$/', trim($line), $match) === 1) {
            $headers[] = trim((string) ($match[1] ?? ''));
        }
    }

    return $headers;
}
