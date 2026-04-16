**FINELLA/NOTIFICATIONS**

Notification delivery helpers with basic API endpoints. Supports email via
`finella/mail` and SMS via a pluggable sender interface.

**INSTALLATION**
```bash
composer require finella/notifications
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Notifications\NotificationsServiceProvider`

**CONFIGURATION**
Create `config/notifications/notifications.php`:
```php
return [
    'auto_migrate' => false,
    'table' => 'notifications',
    'default_channel' => 'email',
];
```

**SCHEMA**
```php
use Finella\Notifications\NotificationsSchema;

NotificationsSchema::ensure($pdo);
```

**API ROUTES**
```php
use Finella\Notifications\NotificationsRoutes;

NotificationsRoutes::register($router, [
    'prefix' => '/api/notifications',
    'middleware' => ['auth'],
]);
```

Endpoints:
**-** `GET /api/notifications`
**-** `GET /api/notifications/{id}`
**-** `POST /api/notifications/send`

**SMS INTEGRATION**
Bind your own SMS sender to the container:
```php
use Finella\Notifications\SmsSenderInterface;

$app->singleton(SmsSenderInterface::class, fn () => new YourSmsSender());
```

**TESTING**
```bash
php tests/smoke.php
```
