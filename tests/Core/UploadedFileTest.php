<?php

declare(strict_types=1);

use Finella\Http\Request;
use Finella\Http\Stream;
use Finella\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

final class UploadedFileTest extends TestCase
{
    public function testNestedFilesNormalization(): void
    {
        $files = [
            'docs' => [
                'tmp_name' => ['a' => 'tmp_a', 'b' => 'tmp_b'],
                'name' => ['a' => 'a.txt', 'b' => 'b.txt'],
                'type' => ['a' => 'text/plain', 'b' => 'text/plain'],
                'size' => ['a' => 10, 'b' => 20],
                'error' => ['a' => UPLOAD_ERR_OK, 'b' => UPLOAD_ERR_OK],
            ],
        ];

        $normalized = $this->callPrivateStatic(Request::class, 'normalizeFiles', $files);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('docs', $normalized);
        $this->assertInstanceOf(UploadedFile::class, $normalized['docs']['a']);
        $this->assertInstanceOf(UploadedFile::class, $normalized['docs']['b']);
    }

    public function testStoreSanitizesAndEnforcesSize(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'finella-upload-' . uniqid();
        $stream = Stream::fromString('hello');
        $file = new UploadedFile([
            'name' => '../evil.php',
            'type' => 'text/plain',
            'size' => 5,
            'error' => UPLOAD_ERR_OK,
        ], $stream);

        $stored = $file->store($dir, '../evil.php', ['text/plain'], 10, true);
        $this->assertStringStartsWith($dir, $stored);
        $this->assertFileExists($stored);
        $this->assertStringNotContainsString('..', $stored);

        $this->expectException(RuntimeException::class);
        $file->store($dir, 'toolarge.txt', [], 1, true);
    }

    private function callPrivateStatic(string $class, string $method, mixed ...$args): mixed
    {
        $ref = new ReflectionMethod($class, $method);
        $ref->setAccessible(true);
        return $ref->invoke(null, ...$args);
    }
}
