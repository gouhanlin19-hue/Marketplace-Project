<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] != "seller" && $_SESSION["role"] != "admin")) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Item ID is missing.");
}

$item_id = $_GET["id"];
$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

// If admin → delete any item
if ($role == "admin") {
    $sql = "DELETE FROM items WHERE item_id = :item_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["item_id" => $item_id]);

} else {
    // If seller → delete only their own item
    $sql = "DELETE FROM items WHERE item_id = :item_id AND seller_id = :seller_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "item_id" => $item_id,
        "seller_id" => $user_id
    ]);
}

if ($_SESSION['role'] === 'admin') {
    header("Location: ../../admin/admin_dashboard.php");
} else {
    header("Location: ../auth/seller_dashboard.php");
}
exit;