<?php

namespace App\Support;

/**
 * A no-op Redis connection used when Redis is not available (e.g. shared hosting).
 * All commands return safe empty values so existing Redis::get/set/expire/georadius
 * calls silently do nothing instead of throwing ConnectionException.
 */
class NullRedisConnection
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function set($key, $value, ...$args): void {}

    public function setex(string $key, int $ttl, $value): void {}

    public function expire(string $key, int $ttl): void {}

    public function del($keys): void {}

    public function exists(string $key): bool
    {
        return false;
    }

    public function flushall(): void {}

    public function georadius(...$args): array
    {
        return [];
    }

    public function georadiusbymember(...$args): array
    {
        return [];
    }

    public function geoadd(...$args): int
    {
        return 0;
    }

    public function ping(): string
    {
        return 'PONG';
    }

    public function __call(string $method, array $args)
    {
        return null;
    }
}
