<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Base query
$sql = "SELECT items.*, users.first_name, users.last_name
        FROM items
        JOIN users ON items.seller_id = users.user_id
        WHERE items.status = 'available'";

// Add search filter if provided
if (!empty($search)) {
    $sql .= " AND items.item_name LIKE :search";
}

// Add category filter if provided
if (!empty($category)) {
    $sql .= " AND items.item_category = :category";
}

$sql .= " ORDER BY items.created_at DESC";

$stmt = $pdo->prepare($sql);
$params = [];
if (!empty($search)) $params['search'] = '%' . $search . '%';
if (!empty($category)) $params['category'] = $category;
$stmt->execute($params);
$items = $stmt->fetchAll();


// Get first image for each item
$image_map = [];
if (!empty($items)) {
    $ids = array_column($items, 'item_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $img_stmt = $pdo->prepare("SELECT * FROM item_images WHERE item_id IN ($placeholders) GROUP BY item_id");
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
  <title>Browse All</title>
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

      <a href="browse.php" class="browse-btn-link">
        <button class="browse-btn">Browse all</button>
      </a>
    </div>

    <form method="GET" action="browse.php" style="display:contents;">
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

      <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'buyer'): ?>
        <a href="../cart/cart.php" class="icon-link">
        <i class="fa-solid fa-cart-shopping nav-icon"></i>
        </a>
        <?php endif; ?>

      
      

        <?php if (isset($_SESSION['user_id'])): ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/dashboard.php" class="login-link">
                <div class="login-box">
                    <?= htmlspecialchars($_SESSION['first_name']) ?>
                </div>
            </a>

        <?php elseif ($_SESSION['role'] === 'seller'): ?>
            <a href="../auth/seller_dashboard.php" class="login-link">
                <div class="login-box">
                    <?= htmlspecialchars($_SESSION['first_name']) ?>
                </div>
            </a>

        <?php else: ?>
            <a href="../auth/buyer_dashboard.php" class="login-link">
                <div class="login-box">
                    <?= htmlspecialchars($_SESSION['first_name']) ?>
                </div>
            </a>
        <?php endif; ?>

        <?php else: ?>
            <a href="backend/auth/login.php" class="login-link">
                <div class="login-box">login /<br>sign in</div>
            </a>
        <?php endif; ?>
            </div>
    
  </header>

  <!-- TOP CATEGORY BOXES -->
  <section class="browse-top-categories">
    <a href="browse.php?category=rare" style="text-decoration:none;">
        <div class="top-category">Rare</div>
    </a>
    <a href="browse.php?category=high_end" style="text-decoration:none;">
        <div class="top-category">High End</div>
    </a>
    <a href="browse.php?category=regular" style="text-decoration:none;">
        <div class="top-category">Regular</div>
    </a>
    <a href="browse.php" style="text-decoration:none;">
        <div class="top-category">All</div>
    </a>
  </section>

  <hr>

  <section class="browse-section">
    <div class="browse-title-row">
        <h2>
            <?php if (!empty($search)): ?>
                Results for: "<?= htmlspecialchars($search) ?>"
            <?php elseif (!empty($category)): ?>
                <?= $category === 'high_end' ? 'high-end' : ucfirst($category) ?> items
            <?php else: ?>
                All products
            <?php endif; ?>
        </h2>
        <p><?= count($items) ?> product(s) found</p>
    </div>

    <!-- Rare items -->
    <?php
    $rare = [];

    foreach ($items as $item) {
    if ($item['item_category'] === 'rare') {
        $rare[] = $item;
    }
    }
    $high = [];
    foreach ($items as $item) {
    if ($item['item_category'] === 'high_end') {
        $high[] = $item;
    }
    }

    $regular = [];
    foreach ($items as $item) {
    if ($item['item_category'] === 'regular') {
        $regular[] = $item;
    }
    }

?>
    
        <?php if (!empty($rare)): ?>
        <?php if (empty($category)): ?>
            <h3>Rare items</h3>
        <?php endif; ?>
        <div class="browse-grid">
            <?php foreach ($rare as $item): ?>
                <div class="browse-card" onclick="window.location.href='item_details.php?id=<?= $item['item_id'] ?>'">
                    <?php if (isset($image_map[$item['item_id']])): ?>
                        <img src="/Omnes-marketplace-main/<?= htmlspecialchars($image_map[$item['item_id']]) ?>"
                            class="browse-card-image" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="browse-card-image"></div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                    <p><?= htmlspecialchars(substr($item['item_description'], 0, 60)) ?>...</p>
                    <strong><?= number_format($item['price'], 2) ?> €</strong>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($high)): ?>
        <?php if (empty($category)): ?>
            <h3>High-end items</h3>
        <?php endif; ?>
        <div class="browse-grid">
            <?php foreach ($high as $item): ?>
                <div class="browse-card" onclick="window.location.href='item_details.php?id=<?= $item['item_id'] ?>'">
                    <?php if (isset($image_map[$item['item_id']])): ?>
                        <img src="/Omnes-marketplace-main/<?= htmlspecialchars($image_map[$item['item_id']]) ?>"
                            class="browse-card-image" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="browse-card-image"></div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                    <p><?= htmlspecialchars(substr($item['item_description'], 0, 60)) ?>...</p>
                    <strong><?= number_format($item['price'], 2) ?> €</strong>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($regular)): ?>
        <?php if (empty($category)): ?>
            <h3>Regular items</h3>
        <?php endif; ?>
        <div class="browse-grid">
            <?php foreach ($regular as $item): ?>
                <div class="browse-card" onclick="window.location.href='item_details.php?id=<?= $item['item_id'] ?>'">
                    <?php if (isset($image_map[$item['item_id']])): ?>
                        <img src="/Omnes-marketplace-main/<?= htmlspecialchars($image_map[$item['item_id']]) ?>"
                            class="browse-card-image" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="browse-card-image"></div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                    <p><?= htmlspecialchars(substr($item['item_description'], 0, 60)) ?>...</p>
                    <strong><?= number_format($item['price'], 2) ?> €</strong>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <p>No items found.</p>
    <?php endif; ?>

</section>


  <footer class="footer"></footer>

  

</body>
</html>