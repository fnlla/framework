<?php

declare(strict_types=1);

use Finella\Http\UploadedFile;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;

$baseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'finella-upload-smoke-' . uniqid();
$uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads';
@mkdir($uploadDir, 0777, true);

$source = $baseDir . DIRECTORY_SEPARATOR . 'source.tmp';
file_put_contents($source, 'hello');

$file = new UploadedFile([
    'tmp_name' => $source,
    'name' => 'test.txt',
    'type' => 'text/plain',
    'size' => filesize($source),
    'error' => UPLOAD_ERR_OK,
]);

$path1 = $file->store($uploadDir, '../evil.php');
$realUploadDir = realpath($uploadDir);
$realPath1 = realpath($path1);

ok($realUploadDir !== false && $realPath1 !== false, 'paths resolved');
ok(str_starts_with($realPath1, $realUploadDir), 'store() prevents traversal');
ok(!str_contains($path1, '..'), 'no parent traversal in path');

$source2 = $baseDir . DIRECTORY_SEPARATOR . 'source2.tmp';
file_put_contents($source2, 'hello2');
$file2 = new UploadedFile([
    'tmp_name' => $source2,
    'name' => 'test2.txt',
    'type' => 'text/plain',
    'size' => filesize($source2),
    'error' => UPLOAD_ERR_OK,
]);

$path2 = $file2->store($uploadDir, 'a/b.php');
$basename = basename($path2);
ok(!str_contains($basename, '/'), 'filename sanitized (/)');
ok(!str_contains($basename, '\\'), 'filename sanitized (\\)');

$source3 = $baseDir . DIRECTORY_SEPARATOR . 'source3.tmp';
file_put_contents($source3, 'hello3');
$file3 = new UploadedFile([
    'tmp_name' => $source3,
    'name' => 'dup.txt',
    'type' => 'text/plain',
    'size' => filesize($source3),
    'error' => UPLOAD_ERR_OK,
]);
$path3 = $file3->store($uploadDir, 'dup.txt');

$source4 = $baseDir . DIRECTORY_SEPARATOR . 'source4.tmp';
file_put_contents($source4, 'hello4');
$file4 = new UploadedFile([
    'tmp_name' => $source4,
    'name' => 'dup.txt',
    'type' => 'text/plain',
    'size' => filesize($source4),
    'error' => UPLOAD_ERR_OK,
]);
$path4 = $file4->store($uploadDir, 'dup.txt');

ok($path3 !== $path4, 'preventOverwrite generates unique filename');

foreach (glob($uploadDir . DIRECTORY_SEPARATOR . '*') ?: [] as $item) {
    @unlink($item);
}
@unlink($source);
@unlink($source2);
@unlink($source3);
@unlink($source4);
@rmdir($uploadDir);
@rmdir($baseDir);

echo "Upload smoke tests OK\n";

