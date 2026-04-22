<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "buyer") {
    header("Location: ../auth/login.php");
    exit;
}

$search = '';

$buyer_id = $_SESSION["user_id"];
$error = "";
$success = "";

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
$item_sql = "SELECT items.item_id, items.item_name, items.price
             FROM cart_items
             JOIN items ON cart_items.item_id = items.item_id
             WHERE cart_items.cart_id = :cart_id";
$item_stmt = $pdo->prepare($item_sql);
$item_stmt->execute(["cart_id" => $cart_id]);
$cart_items = $item_stmt->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item["price"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_firstname = trim($_POST["delivery_firstname"]);
    $delivery_lastname = trim($_POST["delivery_lastname"]);
    $delivery_address1 = trim($_POST["delivery_address1"]);
    $delivery_address2 = trim($_POST["delivery_address2"]);
    $delivery_city = trim($_POST["delivery_city"]);
    $delivery_postal_code = trim($_POST["delivery_postal_code"]);
    $delivery_country = trim($_POST["delivery_country"]);
    $delivery_phone = trim($_POST["delivery_phone"]);

    $card_type = $_POST["card_type"];
    $card_number = trim($_POST["card_number"]);
    $card_name = trim($_POST["card_name"]);
    $expiration_date = trim($_POST["expiration_date"]);
    $security_code = trim($_POST["security_code"]);

    if (
        empty($delivery_firstname) || empty($delivery_lastname) || empty($delivery_address1) ||
        empty($delivery_city) || empty($delivery_postal_code) || empty($delivery_country) ||
        empty($delivery_phone) || empty($card_type) || empty($card_number) ||
        empty($card_name) || empty($expiration_date) || empty($security_code)
    ) {
        $error = "Please fill in all required fields.";
    } elseif (count($cart_items) == 0) {
        $error = "Your cart is empty.";
    } else {
        // Create order
        $order_sql = "INSERT INTO orders (
            buyer_id, total_price, payment_status,
            delivery_firstname, delivery_lastname, delivery_address1, delivery_address2,
            delivery_city, delivery_postal_code, delivery_country, delivery_phone
        ) VALUES (
            :buyer_id, :total_price, 'approved',
            :delivery_firstname, :delivery_lastname, :delivery_address1, :delivery_address2,
            :delivery_city, :delivery_postal_code, :delivery_country, :delivery_phone
        )";

        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([
            "buyer_id" => $buyer_id,
            "total_price" => $subtotal,
            "delivery_firstname" => $delivery_firstname,
            "delivery_lastname" => $delivery_lastname,
            "delivery_address1" => $delivery_address1,
            "delivery_address2" => $delivery_address2,
            "delivery_city" => $delivery_city,
            "delivery_postal_code" => $delivery_postal_code,
            "delivery_country" => $delivery_country,
            "delivery_phone" => $delivery_phone
        ]);

        $order_id = $pdo->lastInsertId();

        // Create payment
        $payment_sql = "INSERT INTO payments (
            order_id, card_type, card_number, card_name, expiration_date, security_code, status
        ) VALUES (
            :order_id, :card_type, :card_number, :card_name, :expiration_date, :security_code, 'paid'
        )";

        $payment_stmt = $pdo->prepare($payment_sql);
        $payment_stmt->execute([
            "order_id" => $order_id,
            "card_type" => $card_type,
            "card_number" => $card_number,
            "card_name" => $card_name,
            "expiration_date" => $expiration_date,
            "security_code" => $security_code
        ]);

        // Add order items + mark sold
        foreach ($cart_items as $item) {
            $order_item_sql = "INSERT INTO order_items (order_id, item_id, price_at_purchase)
                               VALUES (:order_id, :item_id, :price)";
            $order_item_stmt = $pdo->prepare($order_item_sql);
            $order_item_stmt->execute([
                "order_id" => $order_id,
                "item_id" => $item["item_id"],
                "price" => $item["price"]
            ]);

            $update_item_sql = "UPDATE items SET status = 'sold' WHERE item_id = :item_id";
            $update_item_stmt = $pdo->prepare($update_item_sql);
            $update_item_stmt->execute([
                "item_id" => $item["item_id"]
            ]);
        }

        // Clear cart
        $clear_sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $clear_stmt = $pdo->prepare($clear_sql);
        $clear_stmt->execute(["cart_id" => $cart_id]);

        $success = "Order placed successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
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

  <main class="checkout-page">
  <div style="width:100%; max-width:900px; padding:40px 20px;">
    <h1 style="text-align:center; margin-bottom:30px;">Checkout</h1>

    <?php if (!empty($error)): ?>
      <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <p style="color:green; text-align:center; font-size:18px;"><?= htmlspecialchars($success) ?></p>
      <p style="text-align:center;">
          <a href="../../index.php"><button class="btn dark">Back to Home</button></a>
      </p>
    <?php else: ?>

    <form method="POST" action="">
      <div style="display:flex; gap:40px; align-items:flex-start;">

        <!-- LEFT - Delivery -->
        <div style="flex:1; display:flex; flex-direction:column; gap:14px;">
          <h3 style="color:var(--navy-dark);">Delivery Information</h3>

          <input type="text" name="delivery_firstname" placeholder="First name" required
                 value="<?= htmlspecialchars($_POST['delivery_firstname'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_lastname" placeholder="Last name" required
                 value="<?= htmlspecialchars($_POST['delivery_lastname'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_address1" placeholder="Address line 1" required
                 value="<?= htmlspecialchars($_POST['delivery_address1'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_address2" placeholder="Address line 2"
                 value="<?= htmlspecialchars($_POST['delivery_address2'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_city" placeholder="City" required
                 value="<?= htmlspecialchars($_POST['delivery_city'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_postal_code" placeholder="Postal code" required
                 value="<?= htmlspecialchars($_POST['delivery_postal_code'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_country" placeholder="Country" required
                 value="<?= htmlspecialchars($_POST['delivery_country'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="delivery_phone" placeholder="Phone number" required
                 value="<?= htmlspecialchars($_POST['delivery_phone'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">
        </div>

        <!-- RIGHT - Payment + Summary -->
        <div style="flex:1; display:flex; flex-direction:column; gap:14px;">
          <h3 style="color:var(--navy-dark);">Payment Information</h3>

          <select name="card_type" required
                  style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">
              <option value="">Select card type</option>
              <option value="Visa" <?= ($_POST['card_type'] ?? '') == 'Visa' ? 'selected' : '' ?>>Visa</option>
              <option value="MasterCard" <?= ($_POST['card_type'] ?? '') == 'MasterCard' ? 'selected' : '' ?>>MasterCard</option>
              <option value="American Express" <?= ($_POST['card_type'] ?? '') == 'American Express' ? 'selected' : '' ?>>American Express</option>
              <option value="PayPal" <?= ($_POST['card_type'] ?? '') == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
          </select>

          <input type="text" name="card_number" placeholder="Card number" required
                 value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="card_name" placeholder="Name on card" required
                 value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="expiration_date" placeholder="Expiry date (MM/YY)" required
                 value="<?= htmlspecialchars($_POST['expiration_date'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <input type="text" name="security_code" placeholder="Security code (CVV)" required
                 value="<?= htmlspecialchars($_POST['security_code'] ?? '') ?>"
                 style="padding:12px; border:1px solid var(--soft-gray); border-radius:6px;">

          <h3 style="color:var(--navy-dark); margin-top:10px;">Order Summary</h3>

          <div style="background:var(--card-bg); border:1px solid var(--soft-gray); border-radius:6px; padding:16px;">
              <?php foreach ($cart_items as $item): ?>
                  <p style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px;">
                      <span><?= htmlspecialchars($item['item_name']) ?></span>
                      <span><?= number_format($item['price'], 2) ?> €</span>
                  </p>
              <?php endforeach; ?>
              <hr style="margin:10px 0;">
              <p style="display:flex; justify-content:space-between; font-weight:bold;">
                  <span>Total</span>
                  <span><?= number_format($subtotal, 2) ?> €</span>
              </p>
          </div>
        </div>

      </div>

      <!-- CONFIRM BUTTON -->
      <div style="text-align:center; margin-top:30px;">
          <button type="submit" id="confirm-order-btn" style="width:50%; padding:16px; font-size:18px;">
              Confirm Order
          </button>
      </div>

    </form>

    <?php endif; ?>

  </div>
</main>

</body>
</html>

