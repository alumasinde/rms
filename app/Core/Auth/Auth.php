<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Session\Session;
use PDO;

/**
 * Framework-agnostic authentication against the users table.
 * Handles login, logout, current-user resolution, and lockout tracking.
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user_id';
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private ?array $user = null;
    private bool $resolved = false;

    public function __construct(
        private PDO $db,
        private Session $session
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if ($this->isLocked($user)) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $this->registerFailedAttempt((int) $user['id']);
            return false;
        }

        $this->clearFailedAttempts((int) $user['id']);
        $this->login((int) $user['id']);

        return true;
    }

    private function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    private function registerFailedAttempt(int $userId): void
    {
        $stmt = $this->db->prepare('SELECT failed_attempts FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $newCount = ((int) $stmt->fetchColumn()) + 1;

        $lockedUntil = $newCount >= self::MAX_ATTEMPTS
            ? date('Y-m-d H:i:s', time() + self::LOCKOUT_MINUTES * 60)
            : null;

        $stmt = $this->db->prepare(
            'UPDATE users SET failed_attempts = :count, locked_until = :locked_until WHERE id = :id'
        );
        $stmt->execute([
            'count'        => $newCount,
            'locked_until' => $lockedUntil,
            'id'           => $userId,
        ]);
    }

    private function clearFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id'
        );
        $stmt->execute(['id' => $userId]);
    }

    public function login(int $userId): void
    {
        $this->session->regenerate();
        $this->session->put(self::SESSION_KEY, $userId);
        $this->resolved = false;
    }

    public function logout(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->session->flushAndRegenerate();
        $this->user = null;
        $this->resolved = true;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function id(): ?int
    {
        return $this->user()['id'] ?? null;
    }

    public function user(): ?array
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;
        $userId = $this->session->get(self::SESSION_KEY);

        if ($userId === null) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $this->user = $user ?: null;
    }

    public function organizationId(): ?int
    {
        $user = $this->user();
        return $user['organization_id'] ?? null;
    }
}
