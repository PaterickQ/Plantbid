<?php

namespace App\Repositories;

use App\Database;
use mysqli;

class CommentRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getForAuction(int $auctionId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.content, c.created_at, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.auction_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("i", $auctionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function create(int $auctionId, int $userId, string $content): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO comments (auction_id, user_id, content)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $auctionId, $userId, $content);
        $stmt->execute();
    }

    public function delete(int $commentId, int $auctionId): void
    {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND auction_id = ?");
        $stmt->bind_param("ii", $commentId, $auctionId);
        $stmt->execute();
    }
}
