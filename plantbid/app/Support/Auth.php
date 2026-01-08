<?php

namespace App\Support;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function username(): string
    {
        return $_SESSION['username'] ?? '';
    }

    public static function role(): string
    {
        return $_SESSION['role'] ?? 'user';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect_to('login');
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::check() || !self::isAdmin()) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }
    }
}
