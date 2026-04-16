<?php
declare(strict_types=1);

$argv = $_SERVER['argv'] ?? [];
array_shift($argv);

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    echo "Usage: php scripts/ci/check-mojibake.php [--assets|--all]\n";
    exit(0);
}

$root = dirname(__DIR__, 2);
$targets = ['app', 'packages', 'framework', 'documentation', 'scripts', 'tools', 'ui'];
$scanAssets = in_array('--assets', $argv, true) || in_array('--all', $argv, true);
if ($scanAssets) {
    $targets[] = 'public';
    $targets[] = 'assets';
}

$patterns = [
    'Ã¯Â¿Â½',
    'Ã¢â‚¬â€œ',
    'Ã¢â‚¬â€',
    'Ã¢â‚¬â„¢',
    'Ã¢â‚¬Å“',
    'Ã¢â‚¬Â',
    'Ã¢â‚¬Ëœ',
    'Ã¢â‚¬',
    'Ã¢â‚¬Â¦',
    'Ãƒ',
    'Ã…',
    'Ã„',
    'Ã‚',
];

$patternRegex = '/' . implode('|', array_map('preg_quote', $patterns)) . '/u';
$found = false;
$selfPath = realpath(__FILE__) ?: null;

foreach ($targets as $target) {
    $base = $root . DIRECTORY_SEPARATOR . $target;
    if (!is_dir($base)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        $filePath = $fileInfo->getPathname();
        if ($selfPath !== null && realpath($filePath) === $selfPath) {
            continue;
        }
        $file = new SplFileObject($filePath, 'r');
        $lineNumber = 0;

        while (!$file->eof()) {
            $line = $file->fgets();
            $lineNumber++;
            if ($line === false) {
                continue;
            }

            if (preg_match($patternRegex, $line)) {
                $found = true;
                $relative = ltrim(str_replace($root, '', $filePath), DIRECTORY_SEPARATOR);
                $trimmed = rtrim($line);
                echo $relative . ':' . $lineNumber . ': ' . $trimmed . PHP_EOL;
            }
        }
    }
}

if ($found) {
    fwrite(STDERR, "Mojibake detected.\n");
    exit(1);
}

echo "OK: no mojibake detected.\n";
exit(0);

