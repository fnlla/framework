**FNLLA/SETTINGS**

Key/value settings store for Finella. Useful for runtime configuration in admin panels or multi-tenant apps.

**INSTALLATION**
```bash
composer require fnlla/settings
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Settings\SettingsServiceProvider`

**SCHEMA**
Use the schema helper or migrations:
```php
use Finella\Settings\SettingsSchema;

SettingsSchema::ensure($pdo);
```

Default table name: `settings`

**USAGE**
```php
use Finella\Settings\SettingsStore;

$store = app()->make(SettingsStore::class);
$store->set('site_title', 'My Website');
$title = $store->get('site_title', 'Default');
```

Check readiness:
```php
if (!$store->ready()) {
    // Run SettingsSchema::ensure(...) or migrations.
}
```

**CONFIGURATION**
Optional `config/settings/settings.php`:
```php
return [
    'auto_migrate' => false,
    'table' => 'settings',
];
```

**TESTING**
```bash
php tests/smoke.php
```
