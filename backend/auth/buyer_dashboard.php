<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}
$search = '';

// GET BUYER INFO
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$buyer = $stmt->fetch();

// GET PAST ORDERS
$orders_stmt = $pdo->prepare("
    SELECT orders.*, order_items.price_at_purchase, items.item_name
    FROM orders
    JOIN order_items ON orders.order_id = order_items.order_id
    JOIN items ON order_items.item_id = items.item_id
    WHERE orders.buyer_id = ?
    ORDER BY orders.created_at DESC
");
$orders_stmt->execute([$_SESSION['user_id']]);
$orders = $orders_stmt->fetchAll();

$total_paid = array_sum(array_column($orders, 'price_at_purchase'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account</title>
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
      <a href="logout.php" class="login-link">
          <div class="login-box"><?= htmlspecialchars($_SESSION['first_name']) ?></div>
      </a>
      <?php else: ?>
          <a href="login.php" class="login-link">
              <div class="login-box">login /<br>sign in</div>
          </a>
      <?php endif; ?>
    </div>
  </header>
  <!-- ACCOUNT PAGE -->
  <main class="account-page">

    <!-- LEFT -->
    <section class="account-left">
      <div class="account-top">
        <div class="account-avatar"></div>

        

        <div class="account-user-info">
          <div class="user-box">Name: <?= htmlspecialchars($buyer['first_name'] . ' ' . $buyer['last_name']) ?></div>
            <div class="user-box">Email: <?= htmlspecialchars($buyer['email']) ?></div>
        </div>
      </div>

      <div class="payment-card">
        <div class="payment-title">Payment information</div>

        <div class="payment-content">
          <p>Address: <?= htmlspecialchars($buyer['address_line1'] ?? 'Not set') ?></p>
            <p>City: <?= htmlspecialchars($buyer['city'] ?? 'Not set') ?></p>
            <p>Country: <?= htmlspecialchars($buyer['country'] ?? 'Not set') ?></p>
          <button class="edit-btn" onclick="goToCheckout()">Edit</button>
        </div>
      </div>
    </section>

    <!-- RIGHT -->
    <section class="account-right">
      <div class="orders-title">Past orders</div>

        <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-item">
            <div class="order-image"></div>
            <div class="order-info">
                <h4><?= htmlspecialchars($order['item_name']) ?></h4>
                <p>Price: <?= number_format($order['price_at_purchase'], 2) ?> €</p>
                <p>Status: <?= htmlspecialchars($order['payment_status']) ?></p>
                <p>Date: <?= $order['created_at'] ?></p>
            </div>
        </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No past orders yet.</p>
        <?php endif; ?>

        <div class="orders-total-box">
            <h3>Total paid: <?= number_format($total_paid, 2) ?> €</h3>
        </div>

    
    </section>

  </main>

  <script>

    function goToCheckout() {
      window.location.href = "../../backend/cart/checkout.php";
    }
  </script>

</body>
</html>