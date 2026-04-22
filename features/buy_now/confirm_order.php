<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['item_id']) || !isset($_SESSION['buyer_id'])) {
    die("Session expired or invalid access.");
}

$item_id = intval($_SESSION['item_id']);
$buyer_id = intval($_SESSION['buyer_id']);

$card_type = $_POST['card_type'];
$card_number = preg_replace('/\s+/', '', $_POST['card_number']);
$card_name = trim($_POST['card_name']);
$expiry = trim($_POST['expiry']);
$cvv = $_POST['cvv'];

// VALIDATION
if (!preg_match('/^[0-9]{16}$/', $card_number)) {
    die("Invalid card number (must be 16 digits).");
}

if (!preg_match('/^[0-9]{3}$/', $cvv)) {
    die("Invalid CVV (must be 3 digits).");
}

if (empty($card_name)) {
    die("Card name required.");
}

if (empty($expiry)) {
    die("Expiry date required.");
}

// GET ITEM
$sql_item = "SELECT * FROM items WHERE item_id = $item_id";
$result_item = $conn->query($sql_item);

if ($result_item->num_rows == 0) {
    die("Item not found.");
}

$item = $result_item->fetch_assoc();

if ($item['is_sold'] == 'sold') {
    die("Sorry, this item has already been sold.");
}

$price = $item['price'];

// CREATE ORDER (UPDATED)
$sql_order = "INSERT INTO orders 
(buyer_id, item_id, total_price, purchase_type, payment_status)
VALUES 
($buyer_id, $item_id, $price, 'buy_now', 'paid')";

if (!$conn->query($sql_order)) {
    die("Error creating order: " . $conn->error);
}

// UPDATE ITEM
$sql_update = "UPDATE items SET is_sold = 'sold' WHERE item_id = $item_id";

if (!$conn->query($sql_update)) {
    die("Error updating item status: " . $conn->error);
}

echo "<h2>Payment successful!</h2>";
echo "<p>You have purchased: <strong>" . $item['item_name'] . "</strong></p>";
echo "<p>Final Price: €" . $price . "</p>";
?>