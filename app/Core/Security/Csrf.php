<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Session\Session;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(private Session $session)
    {
    }

    public function token(): string
    {
        if (!$this->session->has(self::SESSION_KEY)) {
            $this->session->put(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }

        return (string) $this->session->get(self::SESSION_KEY);
    }

    public function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        return hash_equals($this->token(), $token);
    }

    public function field(): string
    {
        $token = htmlspecialchars($this->token(), ENT_QUOTES, 'UTF-8');
        return "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$token}\">";
    }
}
