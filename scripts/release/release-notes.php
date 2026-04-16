<?php

declare(strict_types=1);

$args = $argv;
array_shift($args);

$version = null;
$date = null;
$range = null;
$baseTag = null;
$updateChangelog = true;
$changelogPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CHANGELOG.md';

for ($i = 0; $i < count($args); $i++) {
    $arg = $args[$i];
    if ($arg === '--version' && isset($args[$i + 1])) {
        $version = $args[$i + 1];
        $i++;
        continue;
    }
    if ($arg === '--date' && isset($args[$i + 1])) {
        $date = $args[$i + 1];
        $i++;
        continue;
    }
    if ($arg === '--range' && isset($args[$i + 1])) {
        $range = $args[$i + 1];
        $i++;
        continue;
    }
    if ($arg === '--base-tag' && isset($args[$i + 1])) {
        $baseTag = $args[$i + 1];
        $i++;
        continue;
    }
    if ($arg === '--no-update') {
        $updateChangelog = false;
        continue;
    }
}

if ($version === null || $version === '') {
    fwrite(STDERR, "Usage: php scripts/release/release-notes.php --version <x.y.z> [--date YYYY-MM-DD] [--range <git-range>] [--base-tag <tag>] [--no-update]\n");
    exit(1);
}

if ($date === null || $date === '') {
    $date = gmdate('Y-m-d');
}

[$gitOk, $gitVersion] = run('git --version');
if (!$gitOk) {
    fwrite(STDERR, "Git not available. Unable to collect commit history.\n");
    exit(1);
}

if ($range === null || $range === '') {
    [$ok, $tagList] = run('git tag --sort=-creatordate');
    $tags = array_values(array_filter(array_map('trim', explode("\n", $tagList))));
    $lastTag = $tags[0] ?? null;
    if ($lastTag !== null && $lastTag !== '') {
        $range = $lastTag . '..HEAD';
    } else {
        $range = 'HEAD';
    }
}

if ($baseTag === null || $baseTag === '') {
    $baseTag = null;
    if (str_contains($range, '..')) {
        $parts = explode('..', $range, 2);
        $baseTag = $parts[0] !== '' ? $parts[0] : null;
    }
}

$sections = [
    'Added' => [],
    'Changed' => [],
    'Deprecated' => [],
    'Removed' => [],
    'Fixed' => [],
    'Security' => [],
];

[$logOk, $logOutput] = run('git log --pretty=format:%H%x1f%s%x1f%b%x1e ' . escapeshellarg($range));
if (!$logOk) {
    fwrite(STDERR, "Unable to read git log for range: {$range}\n");
    exit(1);
}

$entries = array_filter(explode("\x1e", $logOutput), fn ($item) => trim($item) !== '');

foreach ($entries as $entry) {
    $parts = explode("\x1f", $entry);
    $subject = trim($parts[1] ?? '');
    $body = trim($parts[2] ?? '');
    if ($subject === '') {
        continue;
    }

    [$type, $description, $breaking] = parseConventional($subject, $body);
    $section = mapTypeToSection($type, $description);

    if ($section === null) {
        $section = 'Changed';
    }

    $sections[$section][] = $description;
    if ($breaking && $section !== 'Removed') {
        $sections['Changed'][] = 'BREAKING: ' . $description;
    }
}

foreach ($sections as $key => $items) {
    $unique = array_values(array_unique($items));
    $sections[$key] = $unique;
}

$apiChanged = false;
$apiNote = null;
if ($baseTag !== null) {
    $apiChanged = detectApiChange($baseTag);
    if ($apiChanged) {
        $apiNote = 'Public API changed (see documentation/build/api/public-api.json).';
        $sections['Changed'][] = $apiNote;
    }
}

$releaseBlock = buildReleaseBlock($version, $date, $sections);

if ($updateChangelog) {
    if (!is_file($changelogPath)) {
        fwrite(STDERR, "CHANGELOG.md not found.\n");
        exit(1);
    }
    $content = (string) file_get_contents($changelogPath);
    $content = updateChangelog($content, $releaseBlock);
    file_put_contents($changelogPath, $content);
}

echo "Release notes for {$version} ({$date})\n";
echo $releaseBlock;
echo "\n";
if ($baseTag !== null) {
    echo "API changed: " . ($apiChanged ? 'yes' : 'no') . "\n";
} else {
    echo "API changed: unknown (no base tag)\n";
}

function run(string $command): array
{
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return [false, ''];
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    return [$exit === 0, trim($stdout . ($stderr !== '' ? "\n" . $stderr : ''))];
}

function parseConventional(string $subject, string $body): array
{
    $pattern = '/^([a-zA-Z0-9]+)(\([^)]+\))?(!)?:\s+(.*)$/';
    if (preg_match($pattern, $subject, $matches) === 1) {
        $type = strtolower($matches[1]);
        $description = $matches[4];
        $breaking = $matches[3] === '!';
    } else {
        $type = 'chore';
        $description = $subject;
        $breaking = false;
    }

    if (stripos($body, 'BREAKING CHANGE') !== false || stripos($body, 'BREAKING-CHANGE') !== false) {
        $breaking = true;
    }

    return [$type, $description, $breaking];
}

function mapTypeToSection(string $type, string $description): ?string
{
    return match ($type) {
        'feat' => 'Added',
        'fix' => 'Fixed',
        'security' => 'Security',
        'perf', 'refactor' => 'Changed',
        'docs' => 'Changed',
        'chore', 'build', 'ci', 'test', 'style' => 'Changed',
        'deprecated', 'deprecate' => 'Deprecated',
        'remove', 'removed' => 'Removed',
        'revert' => 'Changed',
        default => null,
    };
}

function buildReleaseBlock(string $version, string $date, array $sections): string
{
    $lines = [];
    $lines[] = "## [{$version}] - {$date}";
    foreach (['Added', 'Changed', 'Deprecated', 'Removed', 'Fixed', 'Security'] as $name) {
        $lines[] = "### {$name}";
        $items = $sections[$name] ?? [];
        foreach ($items as $item) {
            $lines[] = '- ' . $item;
        }
    }
    return implode("\n", $lines);
}

function updateChangelog(string $content, string $releaseBlock): string
{
    $unreleased = "## [Unreleased]\n### Added\n### Changed\n### Deprecated\n### Removed\n### Fixed\n### Security\n";
    $pattern = "/## \\[Unreleased\\][\\s\\S]*?(?=\\n## \\[|\\$)/";
    if (preg_match($pattern, $content) !== 1) {
        return $content . "\n\n" . $releaseBlock . "\n";
    }
    return preg_replace($pattern, $unreleased . "\n" . $releaseBlock . "\n", $content);
}

function detectApiChange(string $baseTag): bool
{
    $root = dirname(__DIR__, 2);
    $snapshotScript = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ci' . DIRECTORY_SEPARATOR . 'public-api-snapshot.php';
    if (!is_file($snapshotScript)) {
        return false;
    }

    [$ok, ] = run('php ' . escapeshellarg($snapshotScript));
    if (!$ok) {
        return false;
    }

    $currentPath = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'public-api.json';
    if (!is_file($currentPath)) {
        return false;
    }

    [$okShow, $baseJson] = run('git show ' . escapeshellarg($baseTag . ':documentation/build/api/public-api.json'));
    if (!$okShow || trim($baseJson) === '') {
        return false;
    }

    $currentJson = (string) file_get_contents($currentPath);
    $current = json_decode($currentJson, true);
    $base = json_decode($baseJson, true);
    if (!is_array($current) || !is_array($base)) {
        return false;
    }
    $currentClasses = $current['classes'] ?? [];
    $baseClasses = $base['classes'] ?? [];
    return $currentClasses !== $baseClasses;
}


