<?php

namespace App\Repositories;

use App\Database;
use mysqli;

class AuctionRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getActiveAuctions(): array
    {
        $sql = "
            SELECT a.*,
                   u.username,
                   COALESCE(MAX(b.amount), a.starting_price) AS current_price
            FROM auctions a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN bids b ON a.id = b.auction_id
            WHERE a.end_time > NOW()
            GROUP BY a.id
            ORDER BY a.end_time ASC
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getArchivedAuctions(): array
    {
        $result = $this->db->query("SELECT * FROM auctions WHERE end_time <= NOW() ORDER BY end_time DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllAuctions(): array
    {
        $sql = "
            SELECT a.id, a.title, a.end_time, u.username AS owner
            FROM auctions a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.end_time DESC
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM auctions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function getByIdWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.username
            FROM auctions a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function getUserAuctions(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM auctions WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createAuction(
        int $userId,
        string $title,
        string $description,
        string $endTime,
        float $startingPrice,
        ?string $imagePath,
        ?string $imageUrl
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO auctions (user_id, title, description, end_time, image_path, image_url, starting_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssd", $userId, $title, $description, $endTime, $imagePath, $imageUrl, $startingPrice);
        $stmt->execute();
    }

    public function updateAuction(
        int $id,
        string $title,
        string $description,
        ?string $imagePath,
        ?string $imageUrl
    ): void {
        $stmt = $this->db->prepare("
            UPDATE auctions
            SET title = ?, description = ?, image_path = ?, image_url = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi", $title, $description, $imagePath, $imageUrl, $id);
        $stmt->execute();
    }

    public function updateAuctionForUser(
        int $id,
        int $userId,
        string $title,
        string $description,
        ?string $imagePath,
        ?string $imageUrl
    ): void {
        $stmt = $this->db->prepare("
            UPDATE auctions
            SET title = ?, description = ?, image_path = ?, image_url = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("ssssii", $title, $description, $imagePath, $imageUrl, $id, $userId);
        $stmt->execute();
    }

    public function deleteAuction(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM auctions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function deleteAuctionForUser(int $id, int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM auctions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
    }

    public function getByIds(array $ids): array
    {
        $ids = array_values(array_filter($ids, 'is_numeric'));
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $sql = "
            SELECT a.*,
                   COALESCE(MAX(b.amount), a.starting_price) AS current_price
            FROM auctions a
            LEFT JOIN bids b ON a.id = b.auction_id
            WHERE a.id IN ($placeholders)
            GROUP BY a.id
            ORDER BY a.end_time ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
