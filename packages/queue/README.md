**FINELLA/QUEUE**

Queue module with synchronous, database, and Redis drivers.

**INSTALLATION**
```bash
composer require finella/queue
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Queue\QueueServiceProvider`

**CONFIGURATION**
`config/queue/queue.php`
**-** `driver` (`sync`, `database`, or `redis`)
**-** `database.table` (default `queue_jobs`)
**-** `database.failed_table` (default `queue_failed_jobs`)
**-** `redis.queue` (default `default`)
**-** `redis.prefix` (default `fnlla:queue:`)

**USAGE**
```php
use Finella\Queue\QueueManager;

$queue = $app->make(QueueManager::class);
$queue->dispatch(new class implements \Finella\Queue\JobInterface {
    public function handle(\Finella\Core\Container $app): void
    {
        // do work
    }
});
```

**WORKER**
```php
use Finella\Queue\QueueWorker;

$worker = new QueueWorker($queue, $app);
$worker->work(1);
```

**NOTES**
The sync driver executes jobs immediately. The database and Redis drivers store jobs and can be
processed by `php bin/finella queue:work`.
The Redis driver requires the `ext-redis` PHP extension.

**TESTING**
```bash
php tests/smoke.php
```
