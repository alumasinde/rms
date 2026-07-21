<?php

declare(strict_types=1);

namespace App\Core\Config;

/**
 * Loads config/*.php files and exposes dot-notation access.
 * e.g. Config::get('database.host')
 */
final class Config
{
    private array $items = [];
    private string $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = rtrim($configPath, '/');
        $this->loadAll();
    }

    private function loadAll(): void
    {
        foreach (glob($this->configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target = &$this->items;

        foreach ($segments as $i => $segment) {
            if ($i === count($segments) - 1) {
                $target[$segment] = $value;
                return;
            }
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }
            $target = &$target[$segment];
        }
    }
}
