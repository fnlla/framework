<?php

declare(strict_types=1);

return function (PDO $pdo): void {
    $envSeeders = getenv('APP_SEEDERS');
    $envSeeders = is_string($envSeeders) ? $envSeeders : '';
    $envSeeders = $envSeeders === ''
        ? []
        : array_filter(array_map('trim', explode(',', $envSeeders)), static fn (string $value): bool => $value !== '');

    $seeders = array_merge([
        // \Database\Seeders\UsersSeeder::class,
    ], $envSeeders);

    foreach ($seeders as $seeder) {
        if (!is_string($seeder) || $seeder === '' || !class_exists($seeder)) {
            continue;
        }
        $instance = new $seeder();
        if ($instance instanceof \Fnlla\\Console\SeederInterface) {
            $instance->run($pdo);
            continue;
        }
        if (method_exists($instance, 'run')) {
            $instance->run($pdo);
        }
    }
};
