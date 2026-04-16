<?php

declare(strict_types=1);

use Finella\Core\ConfigRepository;
use PHPUnit\Framework\TestCase;

final class ConfigRepositoryTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            $this->removeDirectory($path);
        }
        $this->cleanup = [];
        putenv('APP_CONFIG_PATH');
    }

    public function testGetSetForgetDotNotation(): void
    {
        $repo = new ConfigRepository(['app' => ['name' => 'Finella']]);

        $this->assertSame('Finella', $repo->get('app.name'));
        $this->assertNull($repo->get('app.missing'));

        $repo->set('app.env', 'production');
        $this->assertSame('production', $repo->get('app.env'));

        $repo->forget('app.name');
        $this->assertNull($repo->get('app.name'));
    }

    public function testFromDirectoryLoadsNestedConfigs(): void
    {
        $configDir = $this->makeConfigDir();
        $this->writeConfig($configDir . DIRECTORY_SEPARATOR . 'app.php', [
            'name' => 'Finella',
        ]);
        $this->writeConfig($configDir . DIRECTORY_SEPARATOR . 'database.php', [
            'default' => 'sqlite',
        ]);

        $mailDir = $configDir . DIRECTORY_SEPARATOR . 'mail';
        mkdir($mailDir, 0777, true);
        $this->writeConfig($mailDir . DIRECTORY_SEPARATOR . 'index.php', [
            'driver' => 'smtp',
        ]);
        $this->writeConfig($mailDir . DIRECTORY_SEPARATOR . 'mail_settings.php', [
            'from' => 'noreply@example.test',
        ]);

        $this->writeConfig($configDir . DIRECTORY_SEPARATOR . 'routes.php', [
            'skip' => true,
        ]);

        $repo = ConfigRepository::fromDirectory($configDir);

        $this->assertSame('Finella', $repo->get('app.name'));
        $this->assertSame('Finella', $repo->get('name'));
        $this->assertSame('sqlite', $repo->get('database.default'));
        $this->assertSame('smtp', $repo->get('mail.driver'));
        $this->assertSame('noreply@example.test', $repo->get('mail_settings.from'));
        $this->assertNull($repo->get('routes'));
    }

    public function testFromRootUsesAppConfigPath(): void
    {
        $configDir = $this->makeConfigDir();
        $configPath = $configDir . DIRECTORY_SEPARATOR . 'config.php';
        $this->writeConfig($configPath, [
            'env' => 'testing',
        ]);

        putenv('APP_CONFIG_PATH=' . $configPath);

        $repo = ConfigRepository::fromRoot($configDir);

        $this->assertSame('testing', $repo->get('env'));
    }

    private function makeConfigDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'finella-config-' . bin2hex(random_bytes(6));
        mkdir($dir, 0777, true);
        $this->cleanup[] = $dir;

        return $dir;
    }

    private function writeConfig(string $path, array $data): void
    {
        $export = var_export($data, true);
        $contents = "<?php\n\nreturn {$export};\n";
        file_put_contents($path, $contents);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($path);
    }
}
