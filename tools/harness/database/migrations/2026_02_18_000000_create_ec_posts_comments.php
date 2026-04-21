<?php

declare(strict_types=1);

use Fnlla\\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function up(PDO $pdo): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS posts (id SERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
            $pdo->exec('CREATE TABLE IF NOT EXISTS comments (id SERIAL PRIMARY KEY, post_id INT NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
            return;
        }

        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, body TEXT NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
            $pdo->exec('CREATE TABLE IF NOT EXISTS comments (id INTEGER PRIMARY KEY AUTOINCREMENT, post_id INTEGER NOT NULL, body TEXT NOT NULL, created_at TEXT NULL, updated_at TEXT NULL)');
            return;
        }

        $pdo->exec('CREATE TABLE IF NOT EXISTS posts (id BIGINT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS comments (id BIGINT AUTO_INCREMENT PRIMARY KEY, post_id BIGINT NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS comments');
        $pdo->exec('DROP TABLE IF EXISTS posts');
    }
};
