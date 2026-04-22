<?php
session_start();
require_once __DIR__ . '../../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

//ADD SELLER
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_seller'])) {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if email or username exists
        $check_sql = "SELECT * FROM users WHERE email = :email OR username = :username";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([
            "email" => $email,
            "username" => $username
        ]);

        if ($check_stmt->fetch()) {
            $error = "Email or username already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, last_name, email, username, password_hash, role)
                    VALUES (:first_name, :last_name, :email, :username, :password_hash, 'seller')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $email,
                "username" => $username,
                "password_hash" => $password_hash
            ]);

            $success = "Seller created successfully.";
        }
    }
}

// remove Seller

if (isset($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'seller'");
    $stmt->execute([$remove_id]);
    $success = "Seller removed successfully.";
}

$sellers_stmt = $pdo->query("SELECT * FROM users WHERE role = 'seller' ORDER BY first_name");
$sellers = $sellers_stmt->fetchAll();

$items_stmt = $pdo->query("
    SELECT items.*, users.first_name AS seller_first, users.last_name AS seller_last
    FROM items
    JOIN users ON items.seller_id = users.user_id
    ORDER BY items.created_at DESC
");
$items = $items_stmt->fetchAll();

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
  <title>Seller List</title>
  <link rel="stylesheet" href="../style.css">
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

  <?php if (!empty($success)): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="search-bar" style="margin-bottom: 20px; max-width: 400px;">
      <input type="text" id="sellerSearch" placeholder="Search seller by name..." onkeyup="filterSellers()">
    </div>

    <div class="seller-section">
      <div class="section-header">
        <div class="section-title">Seller List</div>
        <button class="add-btn" type="button" onclick="document.getElementById('add-form').style.display = document.getElementById('add-form').style.display === 'none' ? 'block' : 'none'">+ Add Seller</button></div>
        
        <div id="add-form" style="display:none; background:#f5f5f5; padding:20px; margin-top:15px; border-radius:8px; width:100%;">
            <h3 style="margin-bottom:15px;">Add New Seller</h3>
            <form method="POST" action="" style="display:flex; flex-direction:column; gap:10px; max-width:400px;">
            <input type="text" name="first_name" placeholder="First name" required style="padding:10px;">
            <input type="text" name="last_name" placeholder="Last name" required style="padding:10px;">
            <input type="email" name="email" placeholder="Email" required style="padding:10px;">
            <input type="text" name="username" placeholder="Username" required style="padding:10px;">
            <input type="password" name="password" placeholder="Password" required style="padding:10px;">
            <input type="text" name="phone" placeholder="Phone number" style="padding:10px;">
            <input type="text" name="address_line1" placeholder="Address line 1" style="padding:10px;">
            <input type="text" name="address_line2" placeholder="Address line 2" style="padding:10px;">
            <input type="text" name="city" placeholder="City" style="padding:10px;">
            <input type="text" name="postal_code" placeholder="Postal code" style="padding:10px;">
            <input type="text" name="country" placeholder="Country" style="padding:10px;">
            <button type="submit" name="add_seller" class="add-btn">Save Seller</button>
            </form>

    
        
  
    </div>

      <div class="seller-grid" id="seller-grid">
            <?php if (count($sellers) > 0): ?>
                <?php foreach ($sellers as $seller): ?>
                    <div class="seller-card" data-name="<?= htmlspecialchars(strtolower($seller['first_name'] . ' ' . $seller['last_name'])) ?>">
                        <div class="avatar"></div>
                        <div>
                            <p><strong>Username:</strong> <?= htmlspecialchars($seller['username']) ?></p>
                            <p><strong>Name:</strong> <?= htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($seller['email']) ?></p>
                        </div>
                        <a href="?remove_id=<?= $seller['user_id'] ?>"
                        onclick="return confirm('Remove this seller?')"
                        style="margin-left:auto; color:red; font-size:13px;">Remove</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No sellers yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-box">
      <h2 style="margin-bottom: 20px; color: var(--navy-dark);">Products</h2>
        <a href="../backend/items/add_item.php">
            <button class="add-btn" style="float:none; margin-top:0; margin-bottom:20px;">+ Add Product</button>
        </a>

      <table>
        <thead>
          <tr>
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
                <td><?= htmlspecialchars($item['sale_type']) ?></td>
                <td><?= htmlspecialchars($item['seller_first'] . ' ' . $item['seller_last']) ?></td>
                <td>
                    <a href="../backend/items/edit_item.php?id=<?= $item['item_id'] ?>">Edit</a>
                    <a href="../../backend/items/delete_item.php?id=<?= $item['item_id'] ?>"
                       onclick="return confirm('Delete this item?')"
                       style="color:red;">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5">No items yet.</td></tr>
    <?php endif; ?>
</tbody>
      </table>
    </div>

  </div>
</div>

  <script>
    

    function goHome() {
      window.location.href = "../index.php";
    }


    function goBrowse() {
      window.location.href = "../backend/items/browse.php";
    }

    function goLogout() { window.location.href = "../backend/auth/logout.php"; }
  </script>

</body>
</html>