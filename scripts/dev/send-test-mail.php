<?php
declare(strict_types=1);

use Fnlla\Core\Application;
use Fnlla\Mail\Address;
use Fnlla\Mail\MailerInterface;
use Fnlla\Mail\Message;

$root = dirname(__DIR__, 2);
$appRoot = $root . '/tools/harness';
$appRoot = is_dir($appRoot) ? $appRoot : $root;
$autoloadCandidates = [
    $root . '/vendor/autoload.php',
    $root . '/tools/harness/vendor/autoload.php',
];

$autoload = null;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Missing composer autoload. Run composer install.\n");
    exit(1);
}

require $autoload;

if (getenv('APP_ROOT') === false) {
    putenv('APP_ROOT=' . $appRoot);
    $_ENV['APP_ROOT'] = $appRoot;
    $_SERVER['APP_ROOT'] = $appRoot;
}

$bootstrap = $appRoot . '/bootstrap/app.php';
if (is_file($bootstrap)) {
    require $bootstrap;
}

$app = $GLOBALS['Fnlla_app'] ?? null;
if (!$app instanceof Application) {
    fwrite(STDERR, "Unable to bootstrap Fnlla app.\n");
    exit(1);
}

if (!interface_exists(MailerInterface::class) || !$app->has(MailerInterface::class)) {
    fwrite(STDERR, "Mailer is not available. Install fnlla/mail and enable the provider.\n");
    exit(1);
}

$args = $_SERVER['argv'] ?? [];
array_shift($args);

$options = [
    'to' => null,
    'subject' => 'Fnlla test email',
    'text' => 'Hello from Fnlla.',
    'html' => null,
];

foreach ($args as $arg) {
    if (str_starts_with($arg, '--to=')) {
        $options['to'] = trim(substr($arg, 5));
        continue;
    }
    if (str_starts_with($arg, '--subject=')) {
        $options['subject'] = trim(substr($arg, 10));
        continue;
    }
    if (str_starts_with($arg, '--text=')) {
        $options['text'] = trim(substr($arg, 7));
        continue;
    }
    if (str_starts_with($arg, '--html=')) {
        $options['html'] = trim(substr($arg, 7));
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        echo "Usage: php scripts/dev/send-test-mail.php --to=you@example.com [--subject=...] [--text=...] [--html=...]\n";
        exit(0);
    }
}

if ($options['to'] === null || $options['to'] === '') {
    fwrite(STDERR, "Missing --to= email address.\n");
    exit(1);
}

$toParts = array_map('trim', explode(',', $options['to']));
$toParts = array_values(array_filter($toParts, static fn (string $value): bool => $value !== ''));

if ($toParts === []) {
    fwrite(STDERR, "Invalid --to= value.\n");
    exit(1);
}

$config = $app->config();
$fromAddress = (string) $config->get('mail.from.address', 'noreply@example.test');
$fromName = (string) $config->get('mail.from.name', 'Fnlla');

$toAddresses = [];
foreach ($toParts as $address) {
    $toAddresses[] = new Address($address);
}

$message = new Message(
    from: new Address($fromAddress, $fromName !== '' ? $fromName : null),
    to: $toAddresses,
    subject: $options['subject'],
    text: $options['text'],
    html: $options['html'] !== '' ? $options['html'] : null
);

$mailer = $app->make(MailerInterface::class);
$mailer->send($message);

echo "OK: sent test email to " . implode(', ', $toParts) . "\n";
