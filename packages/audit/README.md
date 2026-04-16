**FINELLA/AUDIT**

Audit logging helpers for Finella. Stores simple change history (who/what/when).

**INSTALLATION**
```bash
composer require finella/audit
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Audit\AuditServiceProvider`

**SCHEMA**
```php
use Finella\Audit\AuditSchema;

AuditSchema::ensure($pdo);
```

Default table name: `audit_log`

**USAGE**
```php
use Finella\Audit\AuditLogger;

$logger = app()->make(AuditLogger::class);
$logger->record('post.saved', 'post', 123, ['changed' => ['title']]);
```

**CONFIGURATION**
Optional `config/audit/audit.php`:
```php
return [
    'auto_migrate' => false,
    'table' => 'audit_log',
];
```

**TESTING**
```bash
php tests/smoke.php
```
