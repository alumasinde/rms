<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Cache\Cache;

final class RateLimiter
{
    public function __construct(private Cache $cache)
    {
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds = 60): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds = 60): int
    {
        $attempts = $this->attempts($key) + 1;
        $this->cache->put("rl:{$key}", $attempts, $decaySeconds);
        return $attempts;
    }

    public function attempts(string $key): int
    {
        return (int) $this->cache->get("rl:{$key}", 0);
    }

    public function clear(string $key): void
    {
        $this->cache->forget("rl:{$key}");
    }
}
