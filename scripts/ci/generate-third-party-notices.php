<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$defaultOutput = $root . DIRECTORY_SEPARATOR . 'THIRD_PARTY_NOTICES.md';
$lockFileCandidates = [
    'framework/composer.lock',
    'tools/composer.lock',
    'tools/harness/composer.lock',
];
$lockFiles = array_values(array_filter(
    $lockFileCandidates,
    static function (string $relative) use ($root): bool {
        $path = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        return is_file($path);
    }
));

if ($lockFiles === []) {
    fwrite(STDERR, "No supported lock files found.\n");
    exit(1);
}

/** @var list<string> $argv */
$argv = $_SERVER['argv'] ?? [];
$args = array_slice($argv, 1);

$isCheck = false;
$strictDateCheck = false;
$outputPath = $defaultOutput;
$dateOverride = null;

$printUsage = static function (): void {
    $script = 'php scripts/ci/generate-third-party-notices.php';
    echo "Usage:\n";
    echo "  {$script}                Generate/update THIRD_PARTY_NOTICES.md\n";
    echo "  {$script} --check        Verify THIRD_PARTY_NOTICES.md is up to date\n";
    echo "  {$script} --date=YYYY-MM-DD\n";
    echo "  {$script} --output=path/to/file.md\n";
    echo "\n";
    echo "Check mode ignores the 'Last updated' line by default.\n";
    echo "Use --strict-date-check to compare that line too.\n";
};

foreach ($args as $arg) {
    if ($arg === '--check') {
        $isCheck = true;
        continue;
    }

    if ($arg === '--strict-date-check') {
        $strictDateCheck = true;
        continue;
    }

    if ($arg === '--help' || $arg === '-h') {
        $printUsage();
        exit(0);
    }

    if (str_starts_with($arg, '--output=')) {
        $value = trim(substr($arg, strlen('--output=')));
        if ($value === '') {
            fwrite(STDERR, "Invalid --output value.\n");
            exit(1);
        }
        $outputPath = str_starts_with($value, DIRECTORY_SEPARATOR)
            ? $value
            : $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value);
        continue;
    }

    if (str_starts_with($arg, '--date=')) {
        $dateOverride = trim(substr($arg, strlen('--date=')));
        if ($dateOverride === '') {
            fwrite(STDERR, "Invalid --date value.\n");
            exit(1);
        }
        continue;
    }

    fwrite(STDERR, "Unknown option: {$arg}\n");
    $printUsage();
    exit(1);
}

if ($dateOverride !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOverride) !== 1) {
    fwrite(STDERR, "Invalid --date format. Expected YYYY-MM-DD.\n");
    exit(1);
}

/**
 * @param list<string> $relativeLockFiles
 * @return list<array{name: string, version: string, license: string}>
 */
$collectRows = static function (string $basePath, array $relativeLockFiles): array {
    $rows = [];
    $seen = [];

    foreach ($relativeLockFiles as $relativePath) {
        $lockPath = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (!is_file($lockPath)) {
            fwrite(STDERR, "Missing lock file: {$relativePath}\n");
            exit(1);
        }

        $raw = file_get_contents($lockPath);
        if (!is_string($raw) || $raw === '') {
            fwrite(STDERR, "Unable to read lock file: {$relativePath}\n");
            exit(1);
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            fwrite(STDERR, "Invalid JSON in {$relativePath}: {$exception->getMessage()}\n");
            exit(1);
        }

        foreach (['packages', 'packages-dev'] as $group) {
            $packages = $decoded[$group] ?? [];
            if (!is_array($packages)) {
                continue;
            }

            foreach ($packages as $package) {
                if (!is_array($package)) {
                    continue;
                }

                $name = trim((string) ($package['name'] ?? ''));
                if ($name === '' || str_starts_with($name, 'fnlla/')) {
                    continue;
                }

                $version = trim((string) ($package['version'] ?? 'UNKNOWN'));
                if ($version === '') {
                    $version = 'UNKNOWN';
                }

                $licenses = $package['license'] ?? [];
                if (is_string($licenses)) {
                    $licenses = [$licenses];
                }
                if (!is_array($licenses)) {
                    $licenses = [];
                }

                $normalizedLicenses = [];
                foreach ($licenses as $license) {
                    $value = trim((string) $license);
                    if ($value !== '') {
                        $normalizedLicenses[$value] = true;
                    }
                }

                $licenseLabel = 'UNKNOWN';
                if ($normalizedLicenses !== []) {
                    $licenseNames = array_keys($normalizedLicenses);
                    sort($licenseNames, SORT_STRING);
                    $licenseLabel = implode(', ', $licenseNames);
                }

                $key = $name . '|' . $version . '|' . $licenseLabel;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $rows[] = [
                    'name' => $name,
                    'version' => $version,
                    'license' => $licenseLabel,
                ];
            }
        }
    }

    usort(
        $rows,
        static fn (array $left, array $right): int => [$left['name'], $left['version'], $left['license']]
            <=> [$right['name'], $right['version'], $right['license']]
    );

    return $rows;
};

$rows = $collectRows($root, $lockFiles);
$generatedDate = $dateOverride ?? (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');

$lines = [
    '**THIRD-PARTY NOTICES**',
    '',
    'Fnlla includes third-party open-source components. The list below is generated from Composer lock files and is provided for attribution and compliance purposes.',
    'Licenses are reported by each package; consult the upstream project for full license texts.',
    '',
    "Last updated: {$generatedDate}.",
    'Lock files scanned: ' . implode(', ', array_map(
        static fn (string $path): string => '`' . $path . '`',
        $lockFiles
    )) . '.',
    'First-party `fnlla/*` packages are excluded.',
    '',
    '| Package | Version | License |',
    '| --- | --- | --- |',
];

foreach ($rows as $row) {
    $lines[] = sprintf('| %s | %s | %s |', $row['name'], $row['version'], $row['license']);
}

$generatedContent = implode("\n", $lines) . "\n";

$normalize = static function (string $content, bool $ignoreDate): string {
    $normalized = str_replace(["\r\n", "\r"], "\n", $content);
    if ($ignoreDate) {
        $normalized = preg_replace('/^Last updated:\s+.+\.$/m', 'Last updated: <ignored>.', $normalized) ?? $normalized;
    }
    return $normalized;
};

if ($isCheck) {
    if (!is_file($outputPath)) {
        fwrite(STDERR, "Missing notices file: {$outputPath}\n");
        fwrite(STDERR, "Run `php scripts/ci/generate-third-party-notices.php` to generate it.\n");
        exit(1);
    }

    $existingContent = (string) file_get_contents($outputPath);
    $ignoreDate = !$strictDateCheck;
    if ($normalize($existingContent, $ignoreDate) !== $normalize($generatedContent, $ignoreDate)) {
        fwrite(STDERR, "THIRD_PARTY_NOTICES.md is out of date.\n");
        fwrite(STDERR, "Run `php scripts/ci/generate-third-party-notices.php` and commit the changes.\n");
        exit(1);
    }

    echo "Third-party notices check OK.\n";
    exit(0);
}

if (file_put_contents($outputPath, $generatedContent) === false) {
    fwrite(STDERR, "Failed to write notices file: {$outputPath}\n");
    exit(1);
}

echo "Generated: {$outputPath}\n";
