<?php
function getActiveAuctions() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM auctions WHERE end_time > NOW() ORDER BY end_time ASC");
    return $stmt->fetchAll();
}

function timeRemaining($end_time) {
    $now = new DateTime();
    $end = new DateTime($end_time);
    $diff = $now->diff($end);
    return $diff->format('%d dní %H:%I:%S');
}