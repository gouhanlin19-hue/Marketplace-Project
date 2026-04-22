<?php
session_start();

if (isset($_POST['item_id'])) {
    $_SESSION['item_id'] = $_POST['item_id'];
    $_SESSION['buyer_id'] = 1; // fake logged-in buyer for now

    header("Location: payment.php");
    exit();
} else {
    echo "No item selected.";
}
?>