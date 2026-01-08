<?php

namespace App\Repositories;

use App\Database;
use mysqli;

class BidRepository
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getMaxBid(int $auctionId): ?float
    {
        $stmt = $this->db->prepare("SELECT MAX(amount) AS max_bid FROM bids WHERE auction_id = ?");
        $stmt->bind_param("i", $auctionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row && $row['max_bid'] !== null ? (float) $row['max_bid'] : null;
    }

    public function getBidsForAuction(int $auctionId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.amount, b.bid_time, u.username
            FROM bids b
            JOIN users u ON b.user_id = u.id
            WHERE b.auction_id = ?
            ORDER BY b.bid_time DESC
        ");
        $stmt->bind_param("i", $auctionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createBid(int $auctionId, int $userId, float $amount): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO bids (auction_id, user_id, amount, bid_time)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iid", $auctionId, $userId, $amount);
        $stmt->execute();
    }
}
