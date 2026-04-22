<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Omnes-marketplace-main/index.php');
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: /Omnes-marketplace-main/backend/auth/login.php');
    exit;
}

$buyer_id   = $_SESSION['user_id'];
$item_id    = (int)$_POST['item_id'];
$bid_amount = (float)$_POST['bid_amount'];

// Get current highest bid
$stmt = $pdo->prepare("SELECT MAX(max_bid_amount) as max_bid FROM bids WHERE item_id = ?");
$stmt->execute([$item_id]);
$row         = $stmt->fetch();
$current_max = $row['max_bid'] ?? 0;

// Validate bid
if ($bid_amount <= $current_max) {
    header('Location: /Omnes-marketplace-main/features/auction/place_bid.php?id=' . $item_id . '&error=Your+bid+must+be+higher+than+€' . number_format($current_max, 2));
    exit;
}

// Insert bid
$stmt = $pdo->prepare("INSERT INTO bids (item_id, buyer_id, max_bid_amount, current_bid) VALUES (?, ?, ?, ?)");
$stmt->execute([$item_id, $buyer_id, $bid_amount, $bid_amount]);

// Redirect back with success
header('Location: /Omnes-marketplace-main/features/auction/place_bid.php?id=' . $item_id . '&success=1');
exit;
?>