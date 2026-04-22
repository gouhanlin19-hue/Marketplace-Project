<?php
include("includes/db.php");

if (isset($_GET['id'])) {
    $item_id = (int) $_GET['id'];
} else {
    die("No item selected.");
}

$sql = "SELECT * FROM items WHERE item_id = $item_id";
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
} else {
    die("Item not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $item['item_name'] ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }
        .product-card {
            background: white;
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 { margin-bottom: 10px; }
        .info { margin-bottom: 10px; color: #555; }
        .price {
            font-size: 28px;
            font-weight: bold;
            color: #111;
            margin: 20px 0;
        }
        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 14px;
            background: #eee;
            margin-bottom: 20px;
        }
        form {
            margin-top: 20px;
        }
        input, button {
            padding: 12px;
            margin-top: 10px;
            width: 100%;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background: black;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

<img src="images/<?= $item['image_path'] ?>" width="300">

<div class="product-card">
    <h1><?= $item['item_name'] ?></h1>

    <div class="info"><strong>Brand:</strong> <?= $item['brand'] ?></div>
    <div class="info"><strong>Category:</strong> <?= $item['item_category'] ?></div>
    <div class="info"><strong>Condition:</strong> <?= $item['quality_info'] ?></div>

    <div class="badge">
        Sale Type: <?= strtoupper($item['sale_type']) ?>
    </div>

    <p><?= $item['item_description'] ?></p>

    <div class="price">€<?= $item['price'] ?></div>

    <?php if ($item['status'] == 'sold') : ?>

        <p style="color: red; font-weight: bold; margin-top: 20px;">
            This item has already been sold.
        </p>

        <?php
        $sql = "SELECT * FROM negotiation WHERE item_id = $item_id ORDER BY negotiation_id DESC LIMIT 1";
        $res = $conn->query($sql);

        if (!$res) {
            die("SQL Error: " . $conn->error);
        }

        if ($res->num_rows > 0) {
            $offer = $res->fetch_assoc();
            echo "<p>Last offer: €" . $offer['final_count'] . "</p>";
        }
        ?>

    <?php else : ?>

        <?php if ($item['sale_type'] == 'buy_now') : ?>

            <form action="buy_now.php" method="POST">
                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                <button type="submit">Buy It Now</button>
            </form>

        <?php elseif ($item['sale_type'] == 'negotiation') : ?>

            <form action="make_offer.php" method="POST">
                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                <input type="number" step="0.01" name="offer_amount" placeholder="Enter your offer" required>
                <button type="submit">Submit Offer</button>
            </form>

        <?php elseif ($item['sale_type'] == 'auction') : ?>

            <form action="place_bid.php" method="POST">
                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                <input type="number" step="0.01" name="bid_amount" placeholder="Enter your bid" required>
                <button type="submit">Place Bid</button>
            </form>

        <?php endif; ?>

    <?php endif; ?>

    <?php
    $sql = "SELECT MAX(max_bid_amount) as max_bid FROM bids WHERE item_id = $item_id";
    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    $max_bid = $row['max_bid'];

    if (!empty($max_bid)) {
        echo "<p>Current highest bid: €$max_bid</p>";
    } else {
        echo "<p>No bids yet</p>";
    }
    ?>

</div>

</body>
</html>