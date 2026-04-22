<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit;
}
$search = '';

$buyer_id = $_SESSION["user_id"];

// Get cart
$cart_sql = "SELECT cart_id FROM carts WHERE buyer_id = :buyer_id";
$cart_stmt = $pdo->prepare($cart_sql);
$cart_stmt->execute(["buyer_id" => $buyer_id]);
$cart = $cart_stmt->fetch();

if (!$cart) {
    die("Cart not found.");
}

$cart_id = $cart["cart_id"];

// Get cart items
$sql = "SELECT cart_items.cart_item_id, items.item_id, items.item_name, items.price, items.item_description
        FROM cart_items
        JOIN items ON cart_items.item_id = items.item_id
        WHERE cart_items.cart_id = :cart_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(["cart_id" => $cart_id]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item["price"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart</title>
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

      <a href="cart.php" class="icon-link">
        <i class="fa-solid fa-cart-shopping nav-icon cart-icon"></i>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
      <a href="../auth/logout.php" class="login-link">
          <div class="login-box"><?= htmlspecialchars($_SESSION['first_name']) ?></div>
      </a>
      <?php else: ?>
          <a href="../auth/login.php" class="login-link">
              <div class="login-box">login /<br>sign in</div>
          </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- CART -->
  <main class="cart-page">

    <!-- LEFT -->
    <div class="cart-left-area">
        <h1>Your Cart</h1>
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <div class="item-image"></div>
                    <div class="item-middle">
                        <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                        <p><?= htmlspecialchars(substr($item['item_description'] ?? '', 0, 60)) ?></p>
                        <strong><?= number_format($item['price'], 2) ?> €</strong>
                    </div>
                    <div class="item-right">
                        <p>Qty: 1</p>
                        <a href="remove_from_cart.php?id=<?= $item['cart_item_id'] ?>"
                        onclick="return confirm('Remove this item?')">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
        
    </div>

    <!-- RIGHT -->
    <div class="cart-summary">
      <div class="summary-header">Order summary</div>

      <div class="summary-content">
        <?php
            $shipping = count($items) > 0 ? 20 : 0;
            $tax      = count($items) > 0 ? 40 : 0;
            $total    = $subtotal + $shipping + $tax;
            ?>
            <p><strong>Order Subtotal:</strong> <?= number_format($subtotal, 2) ?> €</p>
            <hr>
            <p><strong>Shipping total:</strong> <?= number_format($shipping, 2) ?> €</p>
            <p><strong>Tax:</strong> <?= number_format($tax, 2) ?> €</p>
            <hr class="bottom-line">
            <p class="total-row"><strong>Total:</strong> <?= number_format($total, 2) ?> €</p>

            <?php if (count($items) > 0): ?>
                <a href="checkout.php" style="display:block;">
                    <button class="checkout-btn" style="width:100%;">Check out</button>
                </a>
            <?php else: ?>
                <button class="checkout-btn" style="width:100%; opacity:0.5;" disabled>Check out</button>
            <?php endif; ?>
      </div>

      
    </div>



  </main>

  

</body>
</html>