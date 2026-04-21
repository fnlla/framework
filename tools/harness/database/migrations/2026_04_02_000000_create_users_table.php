<?php

declare(strict_types=1);

use Fnlla\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function up(PDO $pdo): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, name VARCHAR(120) NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
            return;
        }

        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
            return;
        }

        $pdo->exec('CREATE TABLE IF NOT EXISTS users (id BIGINT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NULL, email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
