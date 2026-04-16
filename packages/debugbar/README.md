**FINELLA/DEBUGBAR**

Modern debugging tools for development environments, with request headers and an embedded in-browser panel styled to align with Finella UI tokens.

**INSTALLATION**
```bash
composer require finella/debugbar
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Debugbar\DebugbarServiceProvider`

**WHAT YOU GET**
**-** Response headers: `X-Debug-Queries`, `X-Debug-Messages`, `X-Debug-Errors`, `X-Debug-Time-Ms`, `X-Debug-Slow-Queries`, `X-Debug-Memory-Mb`
**-** Embedded panel for HTML responses with tabs: Summary, Queries, Timeline, Messages, Errors
**-** Query filter input and slow-query highlighting
**-** Keyboard shortcut: `Ctrl+Shift+D` to toggle panel

**ENV FLAGS (OPTIONAL)**
**-** `DEBUGBAR_UI_ENABLED=1` (default: enabled)
**-** `DEBUGBAR_SLOW_QUERY_MS=25`
**-** `DEBUGBAR_MAX_ROWS=120`

**CAPTURING SQL**
Use `DebugPDO` instead of `PDO` in your database connection:
```php
use Finella\Debugbar\DebugPDO;

$pdo = new DebugPDO($dsn, $user, $pass, $options);
```

**COLLECTING EVENTS MANUALLY**
```php
use Finella\Debugbar\DebugbarCollector;

DebugbarCollector::addMessage('info', 'Invoice generated', ['invoice_id' => 123]);
DebugbarCollector::mark('billing.rendered');
```

**NOTES**
Only enable debugbar in non-production environments.
