<?php

declare(strict_types=1);

if (!function_exists('finella_ensure_php85_runtime')) {
    define('FINELLA_REQUIRED_PHP_VERSION', '8.5.5');
    define('FINELLA_REQUIRED_PHP_VERSION_ID', 80505);

    /**
     * Ensure CLI scripts run on PHP 8.5.5.
     * If current runtime differs, re-exec the current script using a detected PHP 8.5.5 binary.
     */
    function finella_ensure_php85_runtime(): void
    {
        if (PHP_SAPI !== 'cli') {
            return;
        }

        // CI runners frequently provide latest patch-level PHP (8.5.x) rather than exactly 8.5.5.
        // Keep local/dev strict pinning, but allow any 8.5.x runtime in CI pipelines.
        if (finella_is_ci_environment() && PHP_VERSION_ID >= 80500 && PHP_VERSION_ID < 80600) {
            return;
        }

        if (PHP_VERSION_ID === FINELLA_REQUIRED_PHP_VERSION_ID) {
            return;
        }

        if (getenv('FINELLA_PHP_GUARD_DISABLED') === '1') {
            return;
        }

        if (getenv('FINELLA_PHP_REEXEC') === '1') {
            finella_php85_guard_fail(
                'PHP ' . FINELLA_REQUIRED_PHP_VERSION . ' is required, and automatic re-exec failed. Current runtime: '
                . PHP_VERSION
            );
        }

        $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
        $argv = $_SERVER['argv'] ?? [];
        if (!is_string($script) || $script === '' || !is_array($argv)) {
            finella_php85_guard_fail('Unable to determine script path/args for PHP 8.5 re-exec.');
        }

        $phpBinary = finella_find_php85_binary();
        if ($phpBinary === null) {
            finella_php85_guard_fail(
                'PHP ' . FINELLA_REQUIRED_PHP_VERSION . ' is required. Set FINELLA_PHP_BIN to a PHP '
                . FINELLA_REQUIRED_PHP_VERSION
                . ' executable (example: C:\\laragon\\bin\\php\\php-8.5.5-Win32-vs17-x64\\php.exe).'
            );
        }

        putenv('FINELLA_PHP_REEXEC=1');
        $_ENV['FINELLA_PHP_REEXEC'] = '1';
        $_SERVER['FINELLA_PHP_REEXEC'] = '1';

        $parts = [escapeshellarg($phpBinary), escapeshellarg($script)];
        foreach (array_slice($argv, 1) as $arg) {
            $parts[] = escapeshellarg((string) $arg);
        }

        $exitCode = 1;
        passthru(implode(' ', $parts), $exitCode);
        exit(is_int($exitCode) ? $exitCode : 1);
    }

    function finella_find_php85_binary(): ?string
    {
        $candidates = [];

        foreach (['FINELLA_PHP_BIN', 'FINELLA_PHP85_BIN'] as $key) {
            $value = getenv($key);
            if (is_string($value) && trim($value) !== '') {
                $candidates[] = trim($value);
            }
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $defaultLaragon = 'C:\\laragon\\bin\\php\\php-8.5.5-Win32-vs17-x64\\php.exe';
            if (is_file($defaultLaragon)) {
                $candidates[] = $defaultLaragon;
            }

            $laragonBins = glob('C:\\laragon\\bin\\php\\php-8.5.5*\\php.exe') ?: [];
            rsort($laragonBins);
            foreach ($laragonBins as $bin) {
                $candidates[] = $bin;
            }
        }

        foreach (finella_find_bins_on_path() as $bin) {
            $candidates[] = $bin;
        }

        $seen = [];
        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            $normalized = strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate));
            if (isset($seen[$normalized])) {
                continue;
            }
            $seen[$normalized] = true;

            if (!is_file($candidate)) {
                continue;
            }

            $versionId = finella_read_php_version_id($candidate);
            if ($versionId !== null && $versionId === FINELLA_REQUIRED_PHP_VERSION_ID) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    function finella_find_bins_on_path(): array
    {
        $path = getenv('PATH');
        if (!is_string($path) || trim($path) === '') {
            return [];
        }

        $names = DIRECTORY_SEPARATOR === '\\'
            ? ['php8.5.5.exe', 'php8.5.exe', 'php85.exe', 'php.exe', 'php8.5.5', 'php8.5', 'php85', 'php']
            : ['php8.5.5', 'php8.5', 'php85', 'php'];

        $bins = [];
        foreach (explode(PATH_SEPARATOR, $path) as $dir) {
            $dir = trim($dir, " \t\n\r\0\x0B\"'");
            if ($dir === '' || !is_dir($dir)) {
                continue;
            }

            foreach ($names as $name) {
                $full = $dir . DIRECTORY_SEPARATOR . $name;
                if (is_file($full)) {
                    $bins[] = $full;
                }
            }
        }

        return $bins;
    }

    function finella_read_php_version_id(string $binary): ?int
    {
        $command = escapeshellarg($binary) . ' -r ' . escapeshellarg('echo PHP_VERSION_ID;');
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);
        if (!is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            return null;
        }

        $output = trim((string) $stdout);
        if ($output === '' || !ctype_digit($output)) {
            return null;
        }

        return (int) $output;
    }

    function finella_is_ci_environment(): bool
    {
        $ci = getenv('CI');
        if (!is_string($ci) || $ci === '') {
            return false;
        }

        $normalized = strtolower(trim($ci));
        return $normalized === '1' || $normalized === 'true' || $normalized === 'yes';
    }

    function finella_php85_guard_fail(string $message): void
    {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

finella_ensure_php85_runtime();
