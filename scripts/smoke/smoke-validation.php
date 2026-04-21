<?php

declare(strict_types=1);

require dirname(__DIR__) . '/ensure-php85.php';

$root = dirname(__DIR__, 2);
$appDir = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'harness';
$autoload = $appDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing tools/harness/vendor/autoload.php. Run composer install in tools/harness.\n");
    exit(1);
}

require $autoload;

use Fnlla\Core\ExceptionHandler;
use Fnlla\Http\Request;
use Fnlla\Http\Stream;
use Fnlla\Http\UploadedFile;
use Fnlla\Http\Uri;
use Fnlla\Support\Validator;
use Fnlla\Support\ValidationException;
use Fnlla\Core\Application;
use Fnlla\Core\ConfigRepository;
use Fnlla\Session\FileSessionStore;
use Fnlla\Session\SessionInterface;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$handler = new ExceptionHandler(false);

// Validator unit rules.
$validator = Validator::make([
    'email' => 'user@example.com',
    'uuid' => '3f0d5f2e-0a1b-4c3d-8f44-0123456789ab',
    'date' => '2026-02-17',
], [
    'email' => 'sometimes|email',
    'uuid' => 'required|uuid',
    'date' => 'required|date:Y-m-d',
]);
ok($validator->passes(), 'validator basic rules pass');

$validator = Validator::make([
    'password' => 'secret',
    'password_confirmation' => 'mismatch',
], [
    'password' => 'required|confirmed',
]);
ok(!$validator->passes(), 'confirmed rule fails when mismatch');

$validator = Validator::make([], [
    'nickname' => 'sometimes|string|min:2',
]);
ok($validator->passes(), 'sometimes skips missing fields');

$validator = Validator::make([
    'date' => '17/02/2026',
], [
    'date' => 'required|date:Y-m-d',
]);
ok(!$validator->passes(), 'date format rule fails on wrong format');

// JSON validation case.
$jsonRequest = new Request(
    'POST',
    new Uri('http://localhost/validate'),
    ['Accept' => 'application/json'],
    Stream::fromString('')
);
$jsonRequest = $jsonRequest->withParsedBody([
    'name' => '',
    'email' => 'invalid-email',
    'items' => [
        ['qty' => 0],
        ['qty' => 2],
    ],
]);

try {
    $jsonRequest->validate([
        'name' => 'required|string|min:3',
        'email' => 'required|email',
        'items' => 'required|array',
        'items.*.qty' => 'required|integer|min:1',
    ]);
    ok(false, 'JSON validation should fail');
} catch (ValidationException $e) {
    $response = $handler->render($e, $jsonRequest);
    ok($response->getStatusCode() === 422, 'JSON validation returns 422');
    ok(str_contains($response->getHeaderLine('Content-Type'), 'application/problem+json'), 'JSON response content-type');
    $payload = json_decode((string) $response->getBody(), true);
    ok(is_array($payload), 'JSON payload is array');
    ok(($payload['type'] ?? '') !== '', 'JSON payload has problem type');
    ok(($payload['title'] ?? '') !== '', 'JSON payload has title');
    ok(($payload['status'] ?? 0) === 422, 'JSON payload has status');
    ok(($payload['detail'] ?? '') !== '', 'JSON payload has detail');
    ok(isset($payload['errors']) && is_array($payload['errors']), 'JSON payload has errors');
    ok(isset($payload['errors']['name']), 'JSON payload has name errors');
    ok(isset($payload['errors']['email']), 'JSON payload has email errors');
    ok(isset($payload['errors']['items.0.qty']), 'JSON payload has nested item errors');
}

// HTML validation + file rule case.
$tmpFile = tempnam(sys_get_temp_dir(), 'Fnlla_upload_');
if ($tmpFile === false) {
    fwrite(STDERR, "FAIL: Unable to create temp file for upload\n");
    exit(1);
}
file_put_contents($tmpFile, str_repeat('a', 2048));

$upload = new UploadedFile([
    'tmp_name' => $tmpFile,
    'name' => 'avatar.png',
    'type' => 'image/png',
    'size' => 2048,
    'error' => UPLOAD_ERR_OK,
]);

$pdfUpload = new UploadedFile([
    'tmp_name' => $tmpFile,
    'name' => 'doc.pdf',
    'type' => '',
    'size' => 2048,
    'error' => UPLOAD_ERR_OK,
]);

$validator = Validator::make([
    'file' => $pdfUpload,
], [
    'file' => 'required|file|mimes:pdf',
]);
ok($validator->passes(), 'mimes extension passes when MIME is missing');

$sessionDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Fnlla_session_' . uniqid();
@mkdir($sessionDir, 0755, true);
$session = new FileSessionStore($sessionDir);
$session->start();

$app = new Application($root, new ConfigRepository([]));
$app->instance(SessionInterface::class, $session);
$GLOBALS['Fnlla_app'] = $app;

$htmlHandler = new ExceptionHandler(false, $app);

$htmlRequest = new Request(
    'POST',
    new Uri('http://localhost/profile'),
    ['Accept' => 'text/html', 'Referer' => 'http://localhost/profile'],
    Stream::fromString('')
);
$htmlRequest = $htmlRequest
    ->withParsedBody(['name' => 'Alice'])
    ->withUploadedFiles(['avatar' => $upload]);

try {
    $htmlRequest->validate([
        'name' => 'required|string|min:2',
        'avatar' => 'required|file|mimes:image/png|size:1024',
    ]);
    ok(false, 'HTML validation should fail on file size');
} catch (ValidationException $e) {
    ok(isset($e->oldInput()['name']), 'old input captured');
    ok(isset($e->oldInput()['avatar']), 'old input includes upload');
    $response = $htmlHandler->render($e, $htmlRequest);
    ok($response->getStatusCode() === 302, 'HTML validation redirects back');
    ok($response->getHeaderLine('Location') !== '', 'HTML redirect has Location header');
    ok(is_array($session->get('_Fnlla_errors')), 'session has flashed errors');
    ok(is_array($session->get('_Fnlla_old')), 'session has flashed old input');
}

@unlink($tmpFile);

echo "Validation smoke tests OK\n";
