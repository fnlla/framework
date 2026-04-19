<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$manifestPath = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'distribution-packages.json';

if (!is_file($manifestPath)) {
    fwrite(STDERR, "Missing distribution manifest: {$manifestPath}\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestPath), true);
if (!is_array($manifest)) {
    fwrite(STDERR, "Invalid distribution manifest JSON.\n");
    exit(1);
}

$publicCore = array_values(array_filter(array_map('strval', is_array($manifest['public_core'] ?? null) ? $manifest['public_core'] : [])));
$privatePro = array_values(array_filter(array_map('strval', is_array($manifest['private_pro'] ?? null) ? $manifest['private_pro'] : [])));
$starterRequired = array_values(array_filter(array_map('strval', is_array($manifest['starter_required'] ?? null) ? $manifest['starter_required'] : [])));
$starterRequiredDev = array_values(array_filter(array_map('strval', is_array($manifest['starter_required_dev'] ?? null) ? $manifest['starter_required_dev'] : [])));

if ($publicCore === [] || $starterRequired === []) {
    fwrite(STDERR, "Manifest must include non-empty public_core and starter_required arrays.\n");
    exit(1);
}

$errors = [];
$warnings = [];

$duplicates = findDuplicates(array_merge($publicCore, $privatePro, $starterRequired, $starterRequiredDev));
if ($duplicates !== []) {
    $errors[] = 'Duplicate package entries in manifest: ' . implode(', ', $duplicates);
}

$overlap = array_values(array_intersect($publicCore, $privatePro));
if ($overlap !== []) {
    $errors[] = 'Package(s) listed as both public_core and private_pro: ' . implode(', ', $overlap);
}

$starterAll = array_values(array_unique(array_merge($starterRequired, $starterRequiredDev)));
foreach ($starterAll as $pkg) {
    if (!in_array($pkg, $publicCore, true)) {
        $errors[] = "{$pkg} is required by starter but not listed in public_core.";
    }
}

$allPackages = array_values(array_unique(array_merge($publicCore, $privatePro)));
foreach ($allPackages as $pkg) {
    $composerPath = resolveComposerPath($root, $pkg);
    if ($composerPath === null) {
        $errors[] = "Unknown package slug in manifest: {$pkg}";
        continue;
    }
    if (!is_file($composerPath)) {
        $errors[] = "Missing composer.json for {$pkg}: {$composerPath}";
        continue;
    }

    $composer = json_decode((string) file_get_contents($composerPath), true);
    if (!is_array($composer)) {
        $errors[] = "Invalid JSON in {$composerPath}";
        continue;
    }

    $name = (string) ($composer['name'] ?? '');
    if ($name !== $pkg) {
        $errors[] = "Package name mismatch in {$composerPath}: expected {$pkg}, found {$name}";
    }

    $description = trim((string) ($composer['description'] ?? ''));
    if ($description === '') {
        $errors[] = "Missing description in {$composerPath}";
    }

    $license = $composer['license'] ?? null;
    if (is_string($license)) {
        $license = trim($license);
    }
    if ((is_string($license) && $license === '') || $license === null || (is_array($license) && $license === [])) {
        $errors[] = "Missing license in {$composerPath}";
    }

    $type = trim((string) ($composer['type'] ?? ''));
    if ($type === '') {
        $errors[] = "Missing package type in {$composerPath}";
    }

    $require = is_array($composer['require'] ?? null) ? $composer['require'] : [];
    if (!isset($require['php'])) {
        $warnings[] = "No explicit php constraint in {$composerPath}";
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, "ERROR: {$error}\n");
    }
    exit(1);
}

foreach ($warnings as $warning) {
    fwrite(STDOUT, "WARN: {$warning}\n");
}

echo "Public distribution manifest check OK.\n";
exit(0);

/**
 * @param list<string> $items
 * @return list<string>
 */
function findDuplicates(array $items): array
{
    $counts = array_count_values($items);
    $dupes = [];
    foreach ($counts as $value => $count) {
        if ($count > 1) {
            $dupes[] = (string) $value;
        }
    }

    sort($dupes);
    return $dupes;
}

function resolveComposerPath(string $root, string $package): ?string
{
    if ($package === 'finella/framework') {
        return $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'composer.json';
    }
    if (!str_starts_with($package, 'finella/')) {
        return null;
    }

    $slug = substr($package, strlen('finella/'));
    if ($slug === '') {
        return null;
    }

    return $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'composer.json';
}
