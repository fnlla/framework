<?php

declare(strict_types=1);

require __DIR__ . '/../../_shared/tests/bootstrap.php';

use Finella\Database\ConnectionManager;
use Finella\Settings\SettingsRepository;
use Finella\Settings\SettingsSchema;
use Finella\Settings\SettingsStore;

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
SettingsSchema::ensure($pdo);

$repo = new SettingsRepository($connections);
$store = new SettingsStore($repo);

$store->set('site_title', 'Finella');
ok($store->get('site_title') === 'Finella', 'settings get');

$store->setMany(['foo' => 'bar', 'baz' => 'qux']);
ok($store->get('foo') === 'bar', 'settings setMany');

$store->delete('foo');
ok($store->get('foo', '') === '', 'settings delete');

echo "Settings smoke tests OK\n";
