<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Cart item ID is missing.");
}

$cart_item_id = $_GET["id"];

$sql = "DELETE FROM cart_items WHERE cart_item_id = :cart_item_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(["cart_item_id" => $cart_item_id]);

header("Location: cart.php");
exit;