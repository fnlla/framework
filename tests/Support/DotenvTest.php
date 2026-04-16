<?php

declare(strict_types=1);

use Finella\Support\Dotenv;
use PHPUnit\Framework\TestCase;

final class DotenvTest extends TestCase
{
    public function testLoadsValuesAndInterpolation(): void
    {
        $contents = <<<'ENV'
# comment
export FINELLA_TEST_APP="Finella"
FINELLA_TEST_GREETING=Hello # inline comment
FINELLA_TEST_INTERP=${FINELLA_TEST_APP}
FINELLA_TEST_DEFAULT=${FINELLA_TEST_MISSING:-fallback}
FINELLA_TEST_SINGLE='literal value'
ENV;

        $keys = [
            'FINELLA_TEST_APP',
            'FINELLA_TEST_GREETING',
            'FINELLA_TEST_INTERP',
            'FINELLA_TEST_DEFAULT',
            'FINELLA_TEST_SINGLE',
            'FINELLA_TEST_MISSING',
        ];
        $snapshot = $this->snapshotEnv($keys);

        $path = $this->writeTempFile($contents);
        $dotenv = new Dotenv();

        try {
            $dotenv->load($path);

            $this->assertSame('Finella', $_ENV['FINELLA_TEST_APP'] ?? null);
            $this->assertSame('Hello', $_ENV['FINELLA_TEST_GREETING'] ?? null);
            $this->assertSame('Finella', $_ENV['FINELLA_TEST_INTERP'] ?? null);
            $this->assertSame('fallback', $_ENV['FINELLA_TEST_DEFAULT'] ?? null);
            $this->assertSame('literal value', $_ENV['FINELLA_TEST_SINGLE'] ?? null);
        } finally {
            @unlink($path);
            $this->restoreEnv($snapshot);
        }
    }

    public function testLoadsMultilineQuotedValues(): void
    {
        $contents = <<<'ENV'
FINELLA_TEST_MULTI="first line
second line"
ENV;

        $keys = ['FINELLA_TEST_MULTI'];
        $snapshot = $this->snapshotEnv($keys);

        $path = $this->writeTempFile($contents);
        $dotenv = new Dotenv();

        try {
            $dotenv->load($path);

            $this->assertSame("first line\nsecond line", $_ENV['FINELLA_TEST_MULTI'] ?? null);
        } finally {
            @unlink($path);
            $this->restoreEnv($snapshot);
        }
    }

    private function writeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'finella-env-');
        if ($path === false) {
            $this->fail('Unable to create temp file for dotenv test.');
        }
        file_put_contents($path, $contents);
        return $path;
    }

    /**
     * @return array<string, string|null>
     */
    private function snapshotEnv(array $keys): array
    {
        $snapshot = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_ENV)) {
                $snapshot[$key] = (string) $_ENV[$key];
                continue;
            }
            if (array_key_exists($key, $_SERVER)) {
                $snapshot[$key] = (string) $_SERVER[$key];
                continue;
            }
            $env = getenv($key);
            $snapshot[$key] = $env === false ? null : (string) $env;
        }

        return $snapshot;
    }

    /**
     * @param array<string, string|null> $snapshot
     */
    private function restoreEnv(array $snapshot): void
    {
        foreach ($snapshot as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key], $_SERVER[$key]);
                putenv($key);
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
