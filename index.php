


<?php
session_start();
require_once __DIR__ . '../db_connect.php';


// Get search + filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Base query
$sql = "SELECT items.*, item_images.image_url 
        FROM items 
        LEFT JOIN item_images ON items.item_id = item_images.item_id
        WHERE items.status = 'available'";

// Search condition
if (!empty($search)) {
    $sql .= " AND item_name LIKE :search";
}

// Category filter
if (!empty($category)) {
    $sql .= " AND item_category = :category";
}

if (empty($search)) {
    $sql .= " ORDER BY created_at DESC LIMIT 4";
}

$stmt = $pdo->prepare($sql);
$params = [];
if (!empty($search)) $params['search'] = '%' . $search . '%';
if (!empty($category)) $params['category'] = $category;
$stmt->execute($params);
$featured_items = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Omnes MarketPlace</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- NAVBAR -->
  <header class="navbar">
    <div class="nav-left">
      <img src="/Omnes-marketplace-main/assets/images/logo.png" 
     style="height:50px; width:auto;">

      <a href="index.php" class="icon-link">
        <i class="fa-solid fa-house nav-icon home-icon"></i>
      </a>

        <a href="backend/items/browse.php" class="browse-btn" style="display:inline-flex; align-items:center; text-decoration:none;">Browse all</a>
    </div>

    
    <form method="GET" action="backend/items/browse.php" style="display:contents;">
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
        <a href="backend/cart/cart.php" class="icon-link">
        <i class="fa-solid fa-cart-shopping nav-icon"></i>
        </a>
        <?php endif; ?>

      
      

        <?php if (isset($_SESSION['user_id'])): ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin/admin_dashboard.php" class="login-link">
                <div class="login-box">
                    <?= htmlspecialchars($_SESSION['first_name']) ?>
                </div>
            </a>

        <?php elseif ($_SESSION['role'] === 'seller'): ?>
            <a href="backend/auth/seller_dashboard.php" class="login-link">
                <div class="login-box">
                    <?= htmlspecialchars($_SESSION['first_name']) ?>
                </div>
            </a>

        <?php else: ?>
            <a href="backend/auth/buyer_dashboard.php" class="login-link">
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

        <!-- HERO -->
        <section class="hero">
            <img src="/Omnes-marketplace-main/assets/images/logo.png" 
                style="height:200px; width:auto;">

            <div class="hero-text">
            <h1>Our Story</h1>
            <h3>Welcome to Omnes MarketPlace</h3>
            <p>
                Omnes MarketPlace is an online shopping platform for the Omnes Education community.
            </p>
            <p>
                Browse electronic products, discover great deals, and enjoy a simple shopping experience.
            </p>
            </div>
        </section>

        <hr>

        <!-- PRODUCT SECTION -->
        <section class="selection">
            <h2>Selection of the products</h2>

            <div class="product-list">

                <?php foreach ($featured_items as $item): ?>
                    <div class="card" onclick="window.location.href='backend/items/item_details.php?id=<?= $item['item_id'] ?>'">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="/Omnes-marketplace-main/<?= htmlspecialchars($item['image_url']) ?>"
                                class="card-image" style="object-fit:contain;">
                        <?php else: ?>
                            <div class="card-image"></div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                        <p><?= htmlspecialchars(substr($item['item_description'], 0, 80)) ?>...</p>
                        <strong><?= number_format($item['price'], 2) ?> €</strong>
                    </div>
                <?php endforeach; ?>


            

            </div>
        </section>

        <!-- BOTTOM INFO -->
        <section class="bottom-info">
            <div class="contact-box">
            <h2>Contact Information</h2>
            <p><strong>Email:</strong> omnesmarketplace@gmail.com</p>
            <p><strong>Phone:</strong> +33 6 12 34 56 78</p>
            <p><strong>Address:</strong> 10 Rue Sextius Michel, Paris, France</p>
            <p><strong>Opening Hours:</strong> Mon - Fri</p>
            </div>

            <div class="vertical-line"></div>

            <div class="map-box">
            <h2>Google Map</h2>
            <iframe
                src="https://www.google.com/maps?q=Paris&output=embed"
                width="100%"
                height="250"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>
            </div>
        </section>

        <footer class="footer"></footer>


</body>
</html>
