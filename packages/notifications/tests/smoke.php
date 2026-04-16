<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Core\ConfigRepository;
use Finella\Database\ConnectionManager;
use Finella\Mail\MailerInterface;
use Finella\Mail\Message;
use Finella\Notifications\NotificationManager;
use Finella\Notifications\NotificationRepository;
use Finella\Notifications\NotificationsSchema;
use Finella\Notifications\NullSmsSender;

function ok(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$connections = new ConnectionManager([
    'driver' => 'sqlite',
    'path' => ':memory:',
]);

$pdo = $connections->connection();
NotificationsSchema::ensure($pdo);

$repo = new NotificationRepository($connections);
$mailer = new class implements MailerInterface {
    public array $sent = [];
    public function send(Message $msg): void
    {
        $this->sent[] = $msg;
    }
};

$config = new ConfigRepository([
    'notifications' => ['default_channel' => 'email'],
    'mail' => ['from' => ['address' => 'noreply@example.test', 'name' => 'Finella']],
]);

$manager = new NotificationManager($repo, $config, $mailer, new NullSmsSender());
$id = $manager->send('email', 'user@example.test', 'Test', 'Hello');

$item = $repo->find($id);
ok($item !== null, 'notification stored');
ok(($item['status'] ?? '') === 'sent', 'notification marked as sent');

echo "Notifications smoke tests OK\n";
