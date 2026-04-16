**FINELLA/DEPLOY**

Deployment utilities for Finella.

**INSTALLATION**
```bash
composer require finella/deploy
```

**COMMANDS**
**-** `deploy:health` - basic environment checks for a deploy target.
**-** `deploy:warmup` - runs cache/provider warmup when available.

**REGISTER COMMANDS**
Add the commands to your `config/console/console.php`:
```php
return [
    'commands' => [
        Finella\Deploy\Commands\DeployHealthCommand::class,
        Finella\Deploy\Commands\DeployWarmupCommand::class,
    ],
];
```

**TESTING**
```bash
php tests/smoke.php
```
