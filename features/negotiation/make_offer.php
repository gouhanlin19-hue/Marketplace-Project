
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

$buyer_id = $_SESSION['user_id'];
$item_id  = intval($_POST['item_id']);
$amount   = floatval($_POST['current_bid']);

if ($amount <= 0) {
    die("Invalid offer amount.");
}

// COUNT OFFERS
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM negotiations WHERE item_id = ? AND buyer_id = ?");
$stmt->execute([$item_id, $buyer_id]);
$row = $stmt->fetch();
$current_round = $row['total'];

if ($current_round >= 5) {
    die("Negotiation closed. Maximum 5 offers reached.");
}

$round_number = $current_round + 1;

// INSERT
$stmt = $pdo->prepare("INSERT INTO negotiations (item_id, buyer_id, final_price, round_count) VALUES (?, ?, ?, ?)");
$stmt->execute([$item_id, $buyer_id, $amount, $round_number]);

header('Location: /Omnes-marketplace-main/features/negotiation/negotiation.php?id=' . $item_id . '&success=1');
exit;
?>