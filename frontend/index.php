


<?php
include("includes/db.php");

// Get search + filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Base query
$sql = "SELECT * FROM items WHERE 1";

// Search condition
if (!empty($search)) {
    $sql .= " AND (item_name LIKE '%$search%' OR brand LIKE '%$search%')";
}

// Category filter
if (!empty($category)) {
    $sql .= " AND item_category = '$category'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Omnes Marketplace</title>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const category = document.getElementById('category');

    if (category) {
        category.addEventListener('change', function () {
            this.form.submit();
        });
    }
});

</script>

    <style>
        body { margin: 0; font-family: Arial; background: #f3f3f3; }

        .header {
            background:#131921;
            color: white;
            padding: 15px 30px;
            font-size: 22px;
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .card {
            position: relative;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.2s;
        }

        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body { padding: 10px; }

        .price {
            color: #B12704;
            font-weight: bold;
            font-size: 18px;
        }

        .type { font-size: 12px; color: gray; }

        .sold-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: red;
            color: white;
            padding: 5px 8px;
            font-size: 12px;
            border-radius: 5px;
        }

        a { text-decoration: none; color: black; }
    
    </style>
</head>

<body>

<div class="header">🛒 Omnes Marketplace</div>

<div style="padding:20px; text-align:center; background:white;">
    <form method="GET" action="index.php">
        
        <input type="text" name="search" placeholder="Search products..."
               style="padding:10px; width:250px;">

        <select name="category" style="padding:10px;">
            <option value="">All Categories</option>
            <option value="smartphone">Smartphones</option>
            <option value="watch">Watches</option>
            <option value="sneakers">Sneakers</option>
            <option value="console">Consoles</option>
            <option value="card">Cards</option>
            <option value="art">Art</option>
        </select>

        <button type="submit" style="padding:10px;">Search</button>
    </form>
</div>

<div class="grid">
<?php while($item = $result->fetch_assoc()): ?>
    <a href="product.php?id=<?= $item['item_id'] ?>">
        <div class="card">

            <?php if($item['status'] == 'sold'): ?>
                <div class="sold-badge">SOLD</div>
            <?php endif; ?>

            <img src="images/<?= $item['image_path'] ?>">

            <div class="card-body">
                <h3><?= $item['item_name'] ?></h3>
                <p><?= $item['brand'] ?></p>
                <p><?= $item['item_category'] ?></p>
                <div class="price">€<?= $item['price'] ?></div>
                <div class="type"><?= $item['sale_type'] ?></div>
            </div>

        </div>
    </a>
<?php endwhile; ?>
</div>

</body>
</html>