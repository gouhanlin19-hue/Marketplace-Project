<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../backend/auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Item ID is missing.");
}

$item_id  = (int)$_GET['id'];
$buyer_id = $_SESSION['user_id'];
$search   = '';

// Get item
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ? AND status = 'available'");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found or not available.");
}

// Get current highest bid
$stmt = $pdo->prepare("SELECT MAX(current_bid) as highest FROM bids WHERE item_id = ?");
$stmt->execute([$item_id]);
$highest = $stmt->fetch();
$current_highest = $highest['highest'] ?? $item['price'];

// Get bid history
$stmt = $pdo->prepare("
    SELECT bids.*, users.first_name, users.last_name 
    FROM bids 
    JOIN users ON bids.buyer_id = users.user_id
    WHERE bids.item_id = ? 
    ORDER BY bids.current_bid DESC
");
$stmt->execute([$item_id]);
$all_bids = $stmt->fetchAll();

// Get total bids
$total_bids = count($all_bids);

// Get auction end date
$bid_end_date = $item['bid_end_date'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction - <?= htmlspecialchars($item['item_name']) ?></title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
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

<div style="max-width:1100px; margin:40px auto; display:flex; gap:40px; padding:0 20px;">

    <!-- IMAGE -->
    <div style="width:500px; flex-shrink:0;">
        <?php
        $img_stmt = $pdo->prepare("SELECT image_url FROM item_images WHERE item_id = ? LIMIT 1");
        $img_stmt->execute([$item_id]);
        $img = $img_stmt->fetch();
        ?>
        <?php if ($img): ?>
            <img src="/Omnes-marketplace-main/<?= htmlspecialchars($img['image_url']) ?>"
                 style="width:100%; border-radius:15px; object-fit:contain;">
        <?php else: ?>
            <div style="width:100%; height:400px; background:#dfe8d8; border-radius:15px;"></div>
        <?php endif; ?>
    </div>

    <!-- INFO -->
    <div class="table-box" style="flex:1;">

        <h2 style="font-size:26px; margin-bottom:10px;">
            <?= htmlspecialchars($item['item_name']) ?>
        </h2>
        <p style="color:#666; margin-bottom:15px;">
            <?= htmlspecialchars($item['item_description']) ?>
        </p>

        <div style="font-size:30px; color:#B12704; font-weight:bold; margin:15px 0;">
            € <span id="currentBid"><?= number_format($current_highest, 2) ?></span>
        </div>

        <p><strong>Total bids:</strong> <?= $total_bids ?></p>

        <!-- COUNTDOWN -->
        <?php if ($bid_end_date): ?>
            <div style="margin:10px 0; font-weight:bold; color:red;">
                ⏳ Ends in: <span id="countdown">--:--:--</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p style="color:green; margin:10px 0;">Your bid has been placed successfully!</p>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; margin:10px 0;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <!-- BID FORM -->
        <div style="background:#fafafa; padding:20px; border-radius:12px; margin-top:20px;">
            <form action="../../features/auction/process_bid.php" method="POST">
                <input type="hidden" name="item_id" value="<?= $item_id ?>">

                <div class="form-group">
                    <label>Your maximum bid (€)</label>
                    <input type="number" name="bid_amount" step="0.01"
                           min="<?= $current_highest + 1 ?>"
                           placeholder="Enter your bid (€)" required>
                </div>

                <button type="submit" style="width:100%; padding:14px; margin-top:15px;
                        background:linear-gradient(90deg, #FFD814, #F7CA00);
                        border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                    Place Bid
                </button>
            </form>
        </div>

        <!-- BID HISTORY -->
        <div style="margin-top:25px;">
            <h3 style="margin-bottom:10px;">Bid History</h3>
            <?php if (!empty($all_bids)): ?>
                <?php foreach ($all_bids as $bid): ?>
                    <div style="background:#f5f5f5; padding:10px; border-radius:8px; margin-top:5px;">
                        <?php if ($bid['buyer_id'] == $buyer_id): ?>
                            <strong>You</strong>
                        <?php else: ?>
                            <?= htmlspecialchars($bid['first_name']) ?> <?= htmlspecialchars(substr($bid['last_name'], 0, 1)) ?>.
                        <?php endif; ?>
                        — <strong><?= number_format($bid['current_bid'], 2) ?> €</strong>
                        <span style="font-size:12px; color:#999;"><?= $bid['created_at'] ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background:#f5f5f5; padding:10px; border-radius:8px;">
                    No bids yet — be the first!
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
<?php if ($bid_end_date): ?>
const endTime = new Date("<?= $bid_end_date ?>").getTime();

const timer = setInterval(() => {
    const now = new Date().getTime();
    const diff = endTime - now;

    if (diff <= 0) {
        document.getElementById("countdown").innerText = "Auction ended";
        clearInterval(timer);
        return;
    }

    const h = Math.floor(diff / (1000 * 60 * 60));
    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const s = Math.floor((diff % (1000 * 60)) / 1000);

    document.getElementById("countdown").innerText =
        `${h}:${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
}, 1000);
<?php endif; ?>
</script>

</body>
</html>
