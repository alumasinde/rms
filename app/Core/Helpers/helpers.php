<?php

declare(strict_types=1);

use App\Core\Container\Container;
use App\Core\Security\Csrf;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Container::getInstance()->make(Csrf::class)->field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Container::getInstance()->make(Csrf::class)->token();
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Container::getInstance()->make(\App\Core\Config\Config::class)->get($key, $default);
    }
}
