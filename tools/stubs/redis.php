<?php
declare(strict_types=1);

if (!class_exists('Redis')) {
    class Redis
    {
        public const OPT_READ_TIMEOUT = 1;

        public function connect(string $host, int $port = 6379, float $timeout = 0.0): bool
        {
        }

        public function pconnect(string $host, int $port = 6379, float $timeout = 0.0, ?string $persistent_id = null): bool
        {
        }

        public function auth(mixed $auth): bool
        {
        }

        public function select(int $db): bool
        {
        }

        public function setOption(int $option, mixed $value): bool
        {
        }

        public function set(string $key, mixed $value, mixed $options = null): bool
        {
        }

        public function setex(string $key, int $ttl, mixed $value): bool
        {
        }

        public function get(string $key): string|false
        {
        }

        public function del(string ...$keys): int
        {
        }

        public function exists(string $key): int
        {
        }

        public function flushDB(): bool
        {
        }

        public function scan(?int &$cursor, string $pattern = '', int $count = 0): array|false
        {
        }

        public function eval(string $script, array $args = [], int $numKeys = 0): mixed
        {
        }

        public function multi(): self
        {
        }

        public function exec(): array|false
        {
        }

        public function hMSet(string $key, array $fields): bool
        {
        }

        public function rPush(string $key, mixed ...$values): int
        {
        }

        public function lPush(string $key, mixed ...$values): int
        {
        }

        public function incr(string $key): int|false
        {
        }

        public function hGetAll(string $key): array
        {
        }

        public function zRem(string $key, mixed ...$members): int
        {
        }

        public function zAdd(string $key, mixed ...$args): int|false
        {
        }

        public function zRangeByScore(string $key, string $min, string $max, array $options = []): array
        {
        }
    }
}
