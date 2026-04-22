<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_GET["id"])) {
    die("Item ID is missing.");
}
$search = '';

$item_id = $_GET["id"];

$sql = "SELECT items.*, users.first_name, users.last_name
        FROM items
        JOIN users ON items.seller_id = users.user_id
        WHERE items.item_id = :item_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(["item_id" => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found.");
}

$image_sql = "SELECT * FROM item_images WHERE item_id = :item_id";
$image_stmt = $pdo->prepare($image_sql);
$image_stmt->execute(["item_id" => $item_id]);
$images = $image_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Item Page</title>
  <link rel="stylesheet" href="../../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-left">
        <img src="/Omnes-marketplace-main/assets/images/logo.png" 
     style="height:50px; width:auto;">

        <a href="../../index.php" class="icon-link">
            <i class="fa-solid fa-house nav-icon home-icon"></i>
        </a>

        <a href="../items/browse.php" class="browse-btn" style="display:inline-flex; align-items:center; text-decoration:none;">Browse all</a>
        </div>

        <form method="GET" action="../items/browse.php" style="display:contents;">
        <div class="search-bar">
            <input type="text" name="search"
                    placeholder="Search..."
                    value="<?= htmlspecialchars($search) ?>">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
        </div>
        </form>

        <div class="nav-right">
            <a href="#" class="icon-link">
                <i class="fa-solid fa-bell nav-icon bell-icon"></i>
            </a>

            <a href="../cart/cart.php" class="icon-link">
                <i class="fa-solid fa-cart-shopping nav-icon cart-icon"></i>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../../backend/auth/logout.php" class="login-link">
                <div class="login-box"><?= htmlspecialchars($_SESSION['first_name']) ?></div>
            </a>
            <?php else: ?>
                <a href="../../backend/auth/login.php" class="login-link">
                    <div class="login-box">login /<br>sign in</div>
                </a>
            <?php endif; ?>
        </div>
  </header>

  </div>

  <!-- ITEM CONTENT -->
  <main class="item-page">

    <!-- LEFT -->
    <div class="item-left">

        <?php if (!empty($images)): ?>
        <img src="/Omnes-marketplace-main/<?= htmlspecialchars($images[0]['image_url']) ?>"
         class="main-image" style="object-fit:contain; background:#f5f5f5;">
        <?php else: ?>
            <div class="main-image"></div>
        <?php endif; ?>
    

        <div class="thumbnail-row">
            <div class="arrow">◀</div>
            <?php foreach ($images as $image): ?>
                <img src="/Omnes-marketplace-main/<?= htmlspecialchars($images[0]['image_url']) ?>"
                    class="thumb" style="object-fit:contain; background:#f5f5f5; cursor:pointer;">
            <?php endforeach; ?>
            <div class="arrow">▶</div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="item-right">
      <h1><?= htmlspecialchars($item['item_name']) ?></h1>
        <p class="subtitle">
            <?= ucfirst($item['item_category'] === 'high_end' ? 'High-end' : $item['item_category']) ?>
            —
            <?= $item['sale_type'] === 'buy_now' ? 'Buy Now' : ($item['sale_type'] === 'bid' ? 'Best Offer' : 'Negotiation') ?>
        </p>
        <span class="price"><?= number_format($item['price'], 2) ?> €</span>
        <p><?= htmlspecialchars($item['item_description']) ?></p>


      <div class="availability">
            <?= $item['status'] === 'available' ? 'Available' : 'Sold' ?>
            — Seller: <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
        </div>

      <div class="price-row">
            

                <?php if ($item['status'] === 'available'): ?>
            <?php if ($item['sale_type'] === 'buy_now'): ?>
                <a href="../cart/add_to_cart.php?id=<?= $item['item_id'] ?>">
                    <button class="btn dark">Add to cart</button>
                </a>
            <?php elseif ($item['sale_type'] === 'auction'): ?>
                <a href="../../features/auction/place_bid.php?id=<?= $item['item_id'] ?>">
                    <button class="btn dark">Place bid</button>
                </a>
            <?php elseif ($item['sale_type'] === 'negotiation'): ?>
                <a href="../../features/negotiation/negotiation.php?id=<?= $item['item_id'] ?>">
                    <button class="btn dark">Negotiate</button>
                </a>
            <?php endif; ?>
                <?php else: ?>
                    <button class="btn" disabled style="opacity:0.5;">Sold</button>
                <?php endif; ?>

                
      </div>

      <hr>

      <h3>Product Description</h3>

      <p id="item-description">
        <?php if (!empty($item['quality_info'])): ?>
            <h3>Quality</h3>
            <p><?= htmlspecialchars($item['quality_info']) ?></p>
        <?php endif; ?>

        <?php if (!empty($item['defect_info'])): ?>
            <h3>Defects</h3>
            <p><?= htmlspecialchars($item['defect_info']) ?></p>
        <?php endif; ?>

        <?php if (!empty($item['video_url'])): ?>
            <h3>Video</h3>
            <a href="<?= htmlspecialchars($item['video_url']) ?>" target="_blank">Watch video</a>
        <?php endif; ?>
      </p>
    </div>

    

  </main>

  <!-- JS -->
<script>
function goToAllProducts() {
    window.location.href = "browse.php";
}
</script>

</body>
</html>