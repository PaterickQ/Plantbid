<?php

namespace App\Repositories;

use App\Database;
use mysqli;

class UserRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function create(string $username, string $email, string $passwordHash, string $role): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $username, $email, $passwordHash, $role);
        return $stmt->execute();
    }

    public function getAll(): array
    {
        $result = $this->db->query("
            SELECT id, username, email, role, blocked, created_at
            FROM users
            ORDER BY created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updateRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $userId);
        $stmt->execute();
    }

    public function updateBlocked(int $userId, int $blocked): void
    {
        $stmt = $this->db->prepare("UPDATE users SET blocked = ? WHERE id = ?");
        $stmt->bind_param("ii", $blocked, $userId);
        $stmt->execute();
    }
}
