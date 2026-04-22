<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
$search = '';
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] != "seller" && $_SESSION["role"] != "admin")) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET["id"])) {
    die("Item ID is missing.");
}

$item_id = $_GET["id"];

// STEP 1 - Fetch item first
if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmt->execute([$item_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND seller_id = ?");
    $stmt->execute([$item_id, $_SESSION["user_id"]]);
}
$item = $stmt->fetch();

if (!$item) {
    die("Item not found or access denied.");
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

        if ($_SESSION['role'] === 'admin') {
            $update_sql = "UPDATE items SET
                            item_name = :item_name,
                            item_description = :item_description,
                            item_category = :item_category,
                            sale_type = :sale_type,
                            price = :price,
                            video_url = :video_url,
                            quality_info = :quality_info,
                            defect_info = :defect_info
                        WHERE item_id = :item_id";

            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                "item_name"        => $item_name,
                "item_description" => $item_description,
                "item_category"    => $item_category,
                "sale_type"        => $sale_type,
                "price"            => $price,
                "video_url"        => $video_url,
                "quality_info"     => $quality_info,
                "defect_info"      => $defect_info,
                "item_id"          => $item_id
            ]);

            } else {
                    $update_sql = "UPDATE items SET
                                    item_name = :item_name,
                                    item_description = :item_description,
                                    item_category = :item_category,
                                    sale_type = :sale_type,
                                    price = :price,
                                    video_url = :video_url,
                                    quality_info = :quality_info,
                                    defect_info = :defect_info
                                WHERE item_id = :item_id AND seller_id = :seller_id";

                    $update_stmt = $pdo->prepare($update_sql);
                    $update_stmt->execute([
                        "item_name"        => $item_name,
                        "item_description" => $item_description,
                        "item_category"    => $item_category,
                        "sale_type"        => $sale_type,
                        "price"            => $price,
                        "video_url"        => $video_url,
                        "quality_info"     => $quality_info,
                        "defect_info"      => $defect_info,
                        "item_id"          => $item_id,
                        "seller_id"        => $_SESSION["user_id"]
                    ]);
            }

        $success = "Item updated successfully.";

        $refresh = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
        $refresh->execute([$item_id]);
        $item = $refresh->fetch();
            }
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
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
        <div class="icon" onclick="window.location.href='../items/browse.php'">
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
        <h1>Edit Item</h1>

        <?php if ($error != "") { ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <?php if ($success != "") { ?>
            <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
        <?php } ?>


            <div class="form-page">
                <form method="POST" action="">
                <div class="form-group">
                    <label>Item Name:</label>
                    <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item["item_name"]); ?>">
                </div>

                <div class="form-group">
                    <label>Description:</label><r>
                    <textarea name="item_description" id="item_description"><?php echo htmlspecialchars($item["item_description"]); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="item_category" id="item_category">
                        <option value="rare" <?php if ($item["item_category"] == "rare") echo "selected"; ?>>Rare</option>
                        <option value="high_end" <?php if ($item["item_category"] == "high_end") echo "selected"; ?>>High End</option>
                        <option value="regular" <?php if ($item["item_category"] == "regular") echo "selected"; ?>>Regular</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sale Type:</label>
                    <select name="sale_type" id="sale_type">
                        <option value="buy_now" <?php if ($item["sale_type"] == "buy_now") echo "selected"; ?>>Buy Now</option>
                        <option value="negotiation" <?php if ($item["sale_type"] == "negotiation") echo "selected"; ?>>Negotiation</option>
                        <option value="auction" <?php if ($item["sale_type"] == "auction") echo "selected"; ?>>Auction</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price:</label>
                    <input type="number" step="0.01" name="price" id="price" value="<?php echo $item["price"]; ?>">
                </div>

                <div class="form-group">
                    <label>Video URL:</label>
                    <input type="text" name="video_url" id="video_urel" value="<?php echo htmlspecialchars($item["video_url"] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Quality Info:</label>
                    <textarea name="quality_info" id="quality_info"><?php echo htmlspecialchars($item["quality_info"] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Defect Info:</label>
                    <textarea name="defect_info" id="defect_info"><?php echo htmlspecialchars($item["defect_info"] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="add-btn">Update Item</button>
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