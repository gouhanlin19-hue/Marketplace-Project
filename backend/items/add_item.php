<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

$search = '';
$dashboard_link = "../auth/login.php";

if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] == "admin") {
        $dashboard_link = "../../admin/admin_dashboard.php";
    } elseif ($_SESSION["role"] == "seller") {
        $dashboard_link = "../auth/seller_dashboard.php";
    }
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = trim($_POST["item_name"]);
    $item_description = trim($_POST["item_description"]);
    $item_category = $_POST["item_category"];
    $sale_type = $_POST["sale_type"];
    $price = $_POST["price"];
    $video_url = trim($_POST["video_url"]);
    $quality_info = trim($_POST["quality_info"]);
    $defect_info = trim($_POST["defect_info"]);

    if (empty($item_name) || empty($item_category) || empty($sale_type) || empty($price)) {
        $error = "Please fill in all required fields.";
    } else {
        $sql = "INSERT INTO items 
                (seller_id, item_name, item_description, item_category, sale_type, price, video_url, quality_info, defect_info)
                VALUES
                (:seller_id, :item_name, :item_description, :item_category, :sale_type, :price, :video_url, :quality_info, :defect_info)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            "seller_id" => $_SESSION["user_id"],
            "item_name" => $item_name,
            "item_description" => $item_description,
            "item_category" => $item_category,
            "sale_type" => $sale_type,
            "price" => $price,
            "video_url" => $video_url,
            "quality_info" => $quality_info,
            "defect_info" => $defect_info
        ]);

        $success = "Item added successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display:flex;">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <img src="/Omnes-marketplace-main/assets/images/logo.png" 
        style="height:100px; width:auto;">
        <div class="icon" onclick="window.location.href='../../index.php'">
            <i class="fa-solid fa-house"></i>
        </div>
        <div class="icon" onclick="window.location.href='browse.php'">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div class="icon">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="icon" onclick="window.location.href='../auth/logout.php'">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
    </div>

    <div class="main">
        <div class="title">Add Item</div>

        <?php if (!empty($error)): ?>
            <p style="color:red; margin-bottom:15px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p style="color:green; margin-bottom:15px;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <div class="form-page">
            <form method="POST" action="">

                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="item_name" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="item_description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="item_category" required>
                        <option value="">Select category</option>
                        <option value="rare">Rare</option>
                        <option value="high_end">High End</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sale Type *</label>
                    <select name="sale_type" required>
                        <option value="">Select sale type</option>
                        <option value="buy_now">Buy Now</option>
                        <option value="negotiate">Negotiation</option>
                        <option value="bid">Best Offer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price (€) *</label>
                    <input type="number" step="0.01" name="price" required>
                </div>

                <div class="form-group">
                    <label>Video URL</label>
                    <input type="text" name="video_url">
                </div>

                <div class="form-group">
                    <label>Quality Info</label>
                    <textarea name="quality_info" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Defect Info</label>
                    <textarea name="defect_info" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Item Photos</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="add-btn">Add Item</button>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../../admin/admin_dashboard.php" class="back-btn" style="display:flex; align-items:center; gap: 12px;; margin-left: 350px; text-decoration:none;margin-top: 20px;">Back to dashboard</a>
                    <?php else: ?>
                        <a href="../auth/seller_dashboard.php" class="back-btn" style="display:flex; align-items:center; gap: 12px;; margin-left: 350px; text-decoration:none;margin-top: 20px;">Back to dashboard</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

</body>
</html>