<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$args = $_SERVER['argv'] ?? [];
array_shift($args);

$repo = 'fnlla/framework';
$changelogPath = $root . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
$dryRun = false;
$versionFilter = null;

for ($i = 0, $count = count($args); $i < $count; $i++) {
    $arg = $args[$i];
    if ($arg === '--repo' && isset($args[$i + 1])) {
        $repo = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--changelog' && isset($args[$i + 1])) {
        $changelogPath = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--version' && isset($args[$i + 1])) {
        $versionFilter = trim((string) $args[++$i]);
        continue;
    }
    if ($arg === '--dry-run') {
        $dryRun = true;
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        printUsage();
        exit(0);
    }
}

if (!is_file($changelogPath)) {
    fwrite(STDERR, "Changelog file not found: {$changelogPath}\n");
    exit(1);
}

$raw = file_get_contents($changelogPath);
if (!is_string($raw) || $raw === '') {
    fwrite(STDERR, "Unable to read changelog: {$changelogPath}\n");
    exit(1);
}

$content = normalizeText($raw);
$releaseNotes = parseReleaseNotes($content);

if ($releaseNotes === []) {
    fwrite(STDERR, "No release notes found in {$changelogPath}\n");
    exit(1);
}

$releaseTags = fetchReleaseTags($repo);
if ($releaseTags === []) {
    fwrite(STDERR, "No releases found for {$repo}\n");
    exit(1);
}

if ($versionFilter !== null && $versionFilter !== '') {
    $versionFilter = ltrim($versionFilter, 'vV');
}

$updated = 0;
foreach ($releaseTags as $tag) {
    $version = ltrim($tag, 'vV');
    if ($versionFilter !== null && $versionFilter !== '' && $version !== $versionFilter) {
        continue;
    }
    if (!isset($releaseNotes[$version])) {
        continue;
    }

    $notes = formatReleaseNotes($releaseNotes[$version]);
    validateReleaseNotes($notes);

    if ($dryRun) {
        echo "== {$tag} ==\n";
        echo $notes . "\n";
        continue;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'finella-sync-');
    if ($tmp === false) {
        fwrite(STDERR, "Unable to create temporary file for {$tag}\n");
        exit(1);
    }

    $tmpFile = $tmp . '.md';
    if (!@rename($tmp, $tmpFile)) {
        $tmpFile = $tmp;
    }

    if (file_put_contents($tmpFile, $notes) === false) {
        @unlink($tmpFile);
        fwrite(STDERR, "Unable to write temporary notes file for {$tag}\n");
        exit(1);
    }

    $command = buildGhEditCommand($repo, $tag, $tmpFile);
    $exitCode = passthruWithExitCode($command);
    @unlink($tmpFile);

    if ($exitCode !== 0) {
        fwrite(STDERR, "Failed to update release {$tag}\n");
        exit($exitCode);
    }

    $updated++;
}

if (!$dryRun) {
    echo "Updated {$updated} releases.\n";
}

exit(0);

function printUsage(): void
{
    $usage = <<<TXT
Usage:
  php scripts/release/sync-release-notes.php [options]

Options:
  --repo <owner/repo>   GitHub repository (default: fnlla/framework)
  --changelog <path>    Changelog path (default: CHANGELOG.md)
  --version <x.y.z>     Sync only one version
  --dry-run             Print notes instead of updating releases
  --help, -h            Show this help

TXT;
    echo $usage;
}

function normalizeText(string $content): string
{
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $content = substr($content, 3);
    }
    return str_replace(["\r\n", "\r"], "\n", $content);
}

function parseReleaseNotes(string $changelog): array
{
    $notes = [];

    $lines = explode("\n", $changelog);
    $currentVersion = null;
    $currentBody = [];

    foreach ($lines as $line) {
        $version = extractReleaseVersion($line);
        if ($version !== null) {
            if ($currentVersion !== null) {
                $normalizedVersion = ltrim($currentVersion, 'vV');
                $body = trim(implode("\n", $currentBody));
                if ($normalizedVersion !== '' && strtolower($normalizedVersion) !== 'unreleased' && $body !== '') {
                    $notes[$normalizedVersion] = $body;
                }
            }
            $currentVersion = $version;
            $currentBody = [];
            continue;
        }

        if ($currentVersion !== null) {
            $currentBody[] = $line;
        }
    }

    if ($currentVersion !== null) {
        $normalizedVersion = ltrim($currentVersion, 'vV');
        $body = trim(implode("\n", $currentBody));
        if ($normalizedVersion !== '' && strtolower($normalizedVersion) !== 'unreleased' && $body !== '') {
            $notes[$normalizedVersion] = $body;
        }
    }

    return $notes;
}

function extractReleaseVersion(string $line): ?string
{
    $trimmed = trim($line);
    if (preg_match('/^##\s+\[([^\]]+)\][^\n]*$/', $trimmed, $match) === 1) {
        $version = trim((string) ($match[1] ?? ''));
        return $version !== '' ? $version : null;
    }

    if (preg_match('/^\*\*\[([^\]]+)\][^*]*\*\*$/', $trimmed, $match) === 1) {
        $version = trim((string) ($match[1] ?? ''));
        return $version !== '' ? $version : null;
    }

    return null;
}

function fetchReleaseTags(string $repo): array
{
    $command = 'gh release list -R ' . escapeshellarg($repo) . ' --limit 200 --json tagName';
    $output = captureCommand($command);
    $decoded = json_decode($output, true);
    if (!is_array($decoded)) {
        return [];
    }
    $tags = [];
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }
        $tag = $row['tagName'] ?? '';
        if (is_string($tag) && $tag !== '') {
            $tags[] = $tag;
        }
    }
    return $tags;
}

function formatReleaseNotes(string $notes): string
{
    $labels = ['HIGHLIGHTS', 'ADDED', 'CHANGED', 'DEPRECATED', 'REMOVED', 'FIXED', 'SECURITY'];
    $labelMap = [];
    foreach ($labels as $label) {
        $labelMap[strtolower($label)] = $label;
    }

    $lines = explode("\n", normalizeText($notes));
    $sections = array_fill_keys($labels, []);
    $intro = [];
    $current = null;
    $inCode = false;

    $labelPattern = '/^\s*(#{1,6}\s*)?(\*\*)?([A-Za-z][A-Za-z\s-]*)(\*\*)?\s*:?$/';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (str_starts_with($trimmed, '```')) {
            $inCode = !$inCode;
            if ($current === null) {
                $intro[] = $line;
            } else {
                $sections[$current][] = $line;
            }
            continue;
        }

        if (!$inCode && $trimmed !== '' && preg_match($labelPattern, $trimmed, $match) === 1) {
            $key = strtolower(trim((string) ($match[3] ?? '')));
            if (isset($labelMap[$key])) {
                $current = $labelMap[$key];
                continue;
            }
        }

        if ($trimmed === '') {
            continue;
        }

        $normalized = $line;
        if (!$inCode) {
            $bullet = ltrim($line);
            if (str_starts_with($bullet, '**-** ')) {
                $normalized = '**-** ' . substr($bullet, 6);
            } elseif (str_starts_with($bullet, '\\- ')) {
                $normalized = '**-** ' . substr($bullet, 3);
            } elseif (str_starts_with($bullet, '- ')) {
                $normalized = '**-** ' . substr($bullet, 2);
            } elseif (str_starts_with($bullet, '* ')) {
                $normalized = '**-** ' . substr($bullet, 2);
            }
        }

        if ($current === null) {
            $intro[] = $normalized;
        } else {
            $sections[$current][] = $normalized;
        }
    }

    $output = [];
    if ($intro !== []) {
        $output = $intro;
    }

    foreach ($labels as $label) {
        $content = $sections[$label] ?? [];
        if ($content === []) {
            continue;
        }
        if ($output !== []) {
            $output[] = '';
        }
        $output[] = '**' . $label . '**';
        foreach ($content as $line) {
            $output[] = $line;
        }
    }

    while ($output !== [] && trim((string) end($output)) === '') {
        array_pop($output);
    }

    return rtrim(implode("\n", $output)) . "\n";
}

function validateReleaseNotes(string $notes): void
{
    if (strpos($notes, "\xEF\xBF\xBD") !== false) {
        fwrite(STDERR, "Release notes contain replacement characters (ï¿½). Fix the source text first.\n");
        exit(1);
    }
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $notes) === 1) {
        fwrite(STDERR, "Release notes contain control characters. Fix the source text first.\n");
        exit(1);
    }
}

function buildGhEditCommand(string $repo, string $tag, string $notesFile): string
{
    return implode(' ', [
        'gh release edit',
        escapeshellarg($tag),
        '-R',
        escapeshellarg($repo),
        '--notes-file',
        escapeshellarg($notesFile),
    ]);
}

function captureCommand(string $command): string
{
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        return '';
    }

    $stdout = '';
    $stderr = '';
    if (isset($pipes[1]) && is_resource($pipes[1])) {
        $stdout = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
    }
    if (isset($pipes[2]) && is_resource($pipes[2])) {
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[2]);
    }

    $exitCode = (int) proc_close($process);
    if ($exitCode !== 0 && $stderr !== '') {
        fwrite(STDERR, $stderr . "\n");
    }

    return $stdout;
}

function passthruWithExitCode(string $command): int
{
    passthru($command, $exitCode);
    return (int) $exitCode;
}
