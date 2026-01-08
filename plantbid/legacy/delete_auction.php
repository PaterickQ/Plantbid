<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$redirect = $_GET['redirect'] ?? '';

if ($is_admin) {
  $stmt = $conn->prepare("DELETE FROM auctions WHERE id = ?");
  $stmt->bind_param("i", $id);
} else {
  $stmt = $conn->prepare("DELETE FROM auctions WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $id, $user_id);
}
$stmt->execute();

$safeRedirects = ['admin.php', 'my_auctions.php'];
$target = in_array($redirect, $safeRedirects, true) ? $redirect : 'my_auctions.php';
header("Location: " . $target);
exit;
