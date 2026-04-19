<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$manifestPath = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'distribution-packages.json';

if (!is_file($manifestPath)) {
    fwrite(STDERR, "Missing manifest: {$manifestPath}\n");
    exit(1);
}

$manifest = json_decode(file_get_contents($manifestPath) ?: '', true);
if (!is_array($manifest)) {
    fwrite(STDERR, "Invalid manifest JSON.\n");
    exit(1);
}

$core = $manifest['public_core'] ?? [];
$starterRequired = $manifest['starter_required'] ?? [];
$starterRequiredDev = $manifest['starter_required_dev'] ?? [];
$pro = $manifest['private_pro'] ?? [];

if (!is_array($core) || !is_array($pro) || !is_array($starterRequired) || !is_array($starterRequiredDev)) {
    fwrite(STDERR, "Manifest must contain public_core, private_pro, starter_required, and starter_required_dev arrays.\n");
    exit(1);
}

function listPackages(string $title, array $packages): void
{
    echo $title . " (" . count($packages) . ")\n";
    echo str_repeat('-', strlen($title) + 4) . "\n";
    foreach ($packages as $name) {
        echo "- {$name}\n";
    }
    echo "\n";
}

listPackages('Public core packages', $core);
listPackages('Starter required packages', $starterRequired);
listPackages('Starter required dev packages', $starterRequiredDev);
listPackages('Private pro packages', $pro);

$notPublic = [];
foreach (array_merge($starterRequired, $starterRequiredDev) as $name) {
    if (!in_array($name, $core, true)) {
        $notPublic[] = $name;
    }
}

if ($notPublic !== []) {
    echo "Starter dependency packages missing from public_core:\n";
    foreach ($notPublic as $name) {
        echo "- {$name}\n";
    }
    exit(3);
}

// Basic existence check (framework and packages/*).
$missing = [];
$allManifestPackages = array_values(array_unique(array_merge($core, $starterRequired, $starterRequiredDev, $pro)));
foreach ($allManifestPackages as $name) {
    if ($name === 'finella/framework') {
        if (!is_dir($root . DIRECTORY_SEPARATOR . 'framework')) {
            $missing[] = $name;
        }
        continue;
    }
    $parts = explode('/', $name, 2);
    $slug = $parts[1] ?? '';
    if ($slug === '') {
        $missing[] = $name;
        continue;
    }
    $path = $root . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . $slug;
    if (!is_dir($path)) {
        $missing[] = $name;
    }
}

if ($missing !== []) {
    echo "Missing packages in repo:\n";
    foreach ($missing as $name) {
        echo "- {$name}\n";
    }
    exit(2);
}

echo "All packages listed in the manifest exist in the repo.\n";
