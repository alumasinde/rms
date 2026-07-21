<?php

declare(strict_types=1);

namespace App\Core\Cache;

/**
 * Simple file-based cache for settings and rate limiting.
 */
final class Cache
{
    public function __construct(private string $path)
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->fileFor($key);

        if (!is_file($file)) {
            return $default;
        }

        $payload = unserialize((string) file_get_contents($file));

        if ($payload['expires_at'] !== null && $payload['expires_at'] < time()) {
            @unlink($file);
            return $default;
        }

        return $payload['value'];
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $payload = [
            'value'      => $value,
            'expires_at' => $ttlSeconds !== null ? time() + $ttlSeconds : null,
        ];

        file_put_contents($this->fileFor($key), serialize($payload), LOCK_EX);
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $value = $this->get($key, '__miss__');

        if ($value !== '__miss__') {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    public function forget(string $key): void
    {
        @unlink($this->fileFor($key));
    }

    private function fileFor(string $key): string
    {
        return rtrim($this->path, '/') . '/' . hash('sha256', $key) . '.cache';
    }
}
