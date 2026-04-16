<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$elementsRoot = $root . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'elements';
$assetsDir = $elementsRoot . DIRECTORY_SEPARATOR . 'assets';
$elementsDir = $elementsRoot . DIRECTORY_SEPARATOR . 'elements';

$assets = [
    'ui.css',
    'elements.css',
    'elements.js',
];

if (!is_dir($assetsDir) || !is_dir($elementsDir)) {
    fwrite(STDERR, "Elements or assets directory not found.\n");
    exit(1);
}

$directories = array_filter(glob($elementsDir . DIRECTORY_SEPARATOR . '*'), 'is_dir');
if ($directories === []) {
    fwrite(STDERR, "No element directories found.\n");
    exit(1);
}

$updated = 0;
$missingAssets = [];
$updatedHtml = 0;

foreach ($directories as $dir) {
    foreach ($assets as $asset) {
        $source = $assetsDir . DIRECTORY_SEPARATOR . $asset;
        $target = $dir . DIRECTORY_SEPARATOR . $asset;

        if (!is_file($source)) {
            $missingAssets[$asset] = true;
            continue;
        }

        if (!copy($source, $target)) {
            fwrite(STDERR, "Failed to copy {$asset} to {$dir}.\n");
            continue;
        }

        $updated++;
    }

    $index = $dir . DIRECTORY_SEPARATOR . 'index.html';
    if (!is_file($index)) {
        continue;
    }

    $html = file_get_contents($index);
    if ($html === false) {
        fwrite(STDERR, "Unable to read {$index}.\n");
        continue;
    }

    $newHtml = str_replace(
        ['../../assets/ui.css', '../../assets/elements.css', '../../assets/elements.js'],
        ['ui.css', 'elements.css', 'elements.js'],
        $html
    );

    if ($newHtml !== $html) {
        if (file_put_contents($index, $newHtml) === false) {
            fwrite(STDERR, "Unable to write {$index}.\n");
            continue;
        }
        $updatedHtml++;
    }
}

if ($missingAssets !== []) {
    fwrite(STDERR, "Missing assets: " . implode(', ', array_keys($missingAssets)) . "\n");
}

echo "Synced assets into " . count($directories) . " element folders.\n";
echo "Copied asset files: {$updated}\n";
echo "Updated index.html files: {$updatedHtml}\n";
