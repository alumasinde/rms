<?php

declare(strict_types=1);

namespace App\Core\Session;

use App\Core\Config\Config;

final class Session
{
    private static bool $started = false;

    public function __construct(private Config $config)
    {
        $this->start();
    }

    private function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name((string) $this->config->get('session.name', 'rms_session'));

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => (bool) $this->config->get('session.secure', false),
            'httponly' => (bool) $this->config->get('session.httponly', true),
            'samesite' => (string) $this->config->get('session.samesite', 'Lax'),
        ]);

        session_start();
        self::$started = true;

        $this->regenerateIfExpired();
    }

    private function regenerateIfExpired(): void
    {
        $lifetime = (int) $this->config->get('session.lifetime', 120) * 60;
        $last = $this->get('_last_activity');

        if ($last !== null && (time() - $last) > $lifetime) {
            $this->flushAndRegenerate();
        }

        $this->put('_last_activity', time());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function flushAndRegenerate(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
