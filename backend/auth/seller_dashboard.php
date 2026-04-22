<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
$search = '';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "seller") {
    header("Location: login.php");
    exit;
}

    // ADD AFTER session check
    $seller_id = $_SESSION['user_id'];

    // Get seller info
    $seller_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $seller_stmt->execute([$seller_id]);
    $seller = $seller_stmt->fetch();

    // Get seller's items
    $items_stmt = $pdo->prepare("
        SELECT * FROM items 
        WHERE seller_id = ? 
        ORDER BY created_at DESC
    ");
    $items_stmt->execute([$seller_id]);
    $items = $items_stmt->fetchAll();

    // Calculate total sales
    $sales_stmt = $pdo->prepare("
        SELECT SUM(order_items.price_at_purchase) as total
        FROM order_items
        JOIN items ON order_items.item_id = items.item_id
        WHERE items.seller_id = ?
    ");
    $sales_stmt->execute([$seller_id]);
    $sales = $sales_stmt->fetch();
    $total_sales = $sales['total'] ?? 0;

    $image_map = [];
if (!empty($items)) {
    $ids = array_column($items, 'item_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $img_stmt = $pdo->prepare("SELECT item_id, image_url FROM item_images WHERE item_id IN ($placeholders) GROUP BY item_id");
    $img_stmt->execute($ids);
    foreach ($img_stmt->fetchAll() as $img) {
        $image_map[$img['item_id']] = $img['image_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Detail</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display: flex;">

  <!-- SIDEBAR -->
  <div class="sidebar">
        
        <img src="/Omnes-marketplace-main/assets/images/logo.png" 
        style="height:100px; width:auto;">

        <div class="icon" onclick="goHome()">
            <i class="fa-solid fa-house"></i>
        </div>
        
        <div class="icon" onclick="goBrowse()">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div class="icon">
            <i class="fa-solid fa-bell"></i>
        </div>
        <div class="icon" onclick="goLogout()">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
    </div>

  <!-- MAIN -->
  <div class="main">
    <div class="title">Welcome back <?= htmlspecialchars($seller['first_name']) ?></div>

    <div class="profile" style="display:flex; align-items:center; gap:30px;">
      <div class="avatar"></div>

      <div class="info-box">
        <div class="info"><strong>Name:</strong> <?= htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']) ?></div>
<div class="info"><strong>Email:</strong> <?= htmlspecialchars($seller['email']) ?></div>
<div class="info"><strong>Username:</strong> <?= htmlspecialchars($seller['username']) ?></div>
      </div>
    </div>

    <div class="stats">
      <div class="stat dark">
        <p>Total Sales</p>
        <h2><?= number_format($total_sales, 2) ?> €</h2>
      </div>

      <div class="stat light">
        <p>Total Products</p>
        <h2><?= count($items) ?></h2>
      </div>

      <div class="stat light">
        <p>Seller ID</p>
        <h2>#<?= $seller['user_id'] ?></h2>
      </div>
    </div>

    <div class="table-box">
      <h2 style="margin-bottom: 20px; color: var(--navy-dark);">Products</h2>

      <table>
        <thead>
          <tr>
            <th></th>
            <th>Product</th>
            <th>Name</th>
            <th>Price</th>
            <th>Type</th>
            <th>ID Number</th>
          </tr>
        </thead>
        <tbody>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>
                            <?php if (isset($image_map[$item['item_id']])): ?>
                                <img src="/Omnes-marketplace-main/<?= htmlspecialchars($image_map[$item['item_id']]) ?>"
                                    class="product-img" style="object-fit:contain;">
                            <?php else: ?>
                                <div class="product-img"></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?> €</td>
                        <td><?= $item['sale_type'] === 'buy_now' ? 'Buy Now' : ($item['sale_type'] === 'bid' ? 'Best Offer' : 'Negotiation') ?></td>
                        <td>#<?= $item['item_id'] ?></td>
                        <td>
                            <a href="../items/edit_item.php?id=<?= $item['item_id'] ?>">Edit</a> |
                            <a href="../items/delete_item.php?id=<?= $item['item_id'] ?>"
                            onclick="return confirm('Delete this item?')"
                            style="color:red;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No items yet.</td></tr>
            <?php endif; ?>
        </tbody>
      </table>

      <a href="../items/add_item.php">
        <button class="add-btn">+ Add Product</button>
        </a>
      <div style="clear: both;"></div>
    </div>
  </div>

  <script>
    

    function goHome() {
      window.location.href = "../../index.php";
    }

    function goSellers() {
      window.location.href = "sellers.php";
    }

    function goBrowse() {
      window.location.href = "../items/browse.php";
    }

    function goLogout() {
      window.location.href = "logout.php";
    }
  </script>

</body>
</html>