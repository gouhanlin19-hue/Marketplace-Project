<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Item ID is missing.");
}

$item_id  = (int)$_GET['id'];
$buyer_id = $_SESSION['user_id'];
$search   = '';

// Get item
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found.");
}

// Get round count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM negotiations WHERE item_id = ? AND buyer_id = ?");
$stmt->execute([$item_id, $buyer_id]);
$row         = $stmt->fetch();
$round_count = $row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negotiate</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="navbar">
    <div class="nav-left">
        <img src="/Omnes-marketplace-main/assets/images/logo.png" style="height:50px; width:auto;">
        <a href="../../../index.php" class="icon-link">
            <i class="fa-solid fa-house nav-icon"></i>
        </a>
        <a href="../../backend/items/browse.php" class="browse-btn">Browse all</a>
    </div>
    <div class="nav-right">
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

<main style="max-width:600px; margin:40px auto; padding:0 20px;">

    <a href="../../backend/items/item_details.php?id=<?= $item_id ?>" class="back-btn"
       style="display:inline-flex; align-items:center; margin-bottom:20px; text-decoration:none;">
        ← Back to item
    </a>
    <?php if (isset($_GET['success'])): ?>
    <p style="color:green; margin-bottom:15px;">Your offer has been submitted successfully!</p>
<?php endif; ?>
     
    <div class="table-box">
        <h2 style="color:var(--navy-dark); margin-bottom:20px;">
            Negotiate — <?= htmlspecialchars($item['item_name']) ?>
        </h2>

        <div style="background:var(--light-bg); padding:16px; border-radius:6px; margin-bottom:20px;">
            <p><strong>Asking price:</strong> <?= number_format($item['price'], 2) ?> €</p>
            <p><strong>Round:</strong> <?= $round_count ?> / 5</p>
        </div>

        <?php if ($round_count >= 5): ?>
            <p style="color:red;">Maximum 5 rounds reached. Negotiation closed.</p>
        <?php else: ?>
            <form method="POST" action="make_offer.php"
                  style="display:flex; flex-direction:column; gap:14px;">

                <input type="hidden" name="item_id" value="<?= $item_id ?>">

                <div class="form-group">
                    <label>Your offer (€)</label>
                    <input type="number" name="current_bid" step="0.01"
                           placeholder="Enter your offer" required>
                </div>

                <button type="submit" class="add-btn" style="padding:12px 24px;">
                    Submit Offer
                </button>

            </form>
        <?php endif; ?>
    </div>
</main>

</body>
</html>