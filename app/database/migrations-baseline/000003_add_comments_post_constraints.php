<?php

declare(strict_types=1);

use Finella\Database\MigrationInterface;

return new class implements MigrationInterface {
    public function up(PDO $pdo): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->createIndex($pdo);
            return;
        }

        $this->createIndex($pdo);
        $this->addForeignKey($pdo, $driver);
    }

    public function down(PDO $pdo): void
    {
        $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver !== 'sqlite') {
            $this->dropForeignKey($pdo, $driver);
        }

        $this->dropIndex($pdo, $driver);
    }

    private function createIndex(PDO $pdo): void
    {
        try {
            $pdo->exec('CREATE INDEX comments_post_id_index ON comments (post_id)');
        } catch (Throwable) {
            // Ignore if index already exists or table missing.
        }
    }

    private function addForeignKey(PDO $pdo, string $driver): void
    {
        $sql = 'ALTER TABLE comments ADD CONSTRAINT comments_post_id_fk FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE';
        try {
            $pdo->exec($sql);
        } catch (Throwable) {
            // Ignore if FK already exists or unsupported.
        }
    }

    private function dropForeignKey(PDO $pdo, string $driver): void
    {
        if ($driver === 'pgsql') {
            $sql = 'ALTER TABLE comments DROP CONSTRAINT IF EXISTS comments_post_id_fk';
        } else {
            $sql = 'ALTER TABLE comments DROP FOREIGN KEY comments_post_id_fk';
        }

        try {
            $pdo->exec($sql);
        } catch (Throwable) {
            // Ignore if FK does not exist.
        }
    }

    private function dropIndex(PDO $pdo, string $driver): void
    {
        if ($driver === 'pgsql') {
            $sql = 'DROP INDEX IF EXISTS comments_post_id_index';
        } elseif ($driver === 'sqlite') {
            $sql = 'DROP INDEX IF EXISTS comments_post_id_index';
        } else {
            $sql = 'DROP INDEX comments_post_id_index ON comments';
        }

        try {
            $pdo->exec($sql);
        } catch (Throwable) {
            // Ignore if index does not exist.
        }
    }
};
