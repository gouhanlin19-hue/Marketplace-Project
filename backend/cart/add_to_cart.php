<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Item ID is missing.");
}

$item_id = $_GET["id"];
$buyer_id = $_SESSION["user_id"];

// Get buyer cart
$cart_sql = "SELECT cart_id FROM carts WHERE buyer_id = :buyer_id";
$cart_stmt = $pdo->prepare($cart_sql);
$cart_stmt->execute(["buyer_id" => $buyer_id]);
$cart = $cart_stmt->fetch();

if (!$cart) {
    die("Cart not found.");
}

$cart_id = $cart["cart_id"];

// Check if item already in cart
$check_sql = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND item_id = :item_id";
$check_stmt = $pdo->prepare($check_sql);
$check_stmt->execute([
    "cart_id" => $cart_id,
    "item_id" => $item_id
]);

if (!$check_stmt->fetch()) {
    $insert_sql = "INSERT INTO cart_items (cart_id, item_id, quantity) VALUES (:cart_id, :item_id, 1)";
    $insert_stmt = $pdo->prepare($insert_sql);
    $insert_stmt->execute([
        "cart_id" => $cart_id,
        "item_id" => $item_id
    ]);
}

header("Location: cart.php");
exit;