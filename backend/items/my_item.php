<!-- no use for this file
<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] != "seller" && $_SESSION["role"] != "admin")) {
    header("Location: ../backend/auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];

if ($role == "admin") {
    // Admin sees ALL items
    $sql = "SELECT items.*, users.first_name, users.last_name
            FROM items
            JOIN users ON items.seller_id = users.user_id";
    $stmt = $pdo->query($sql);

} else {
    // Seller sees only their items
    $sql = "SELECT * FROM items WHERE seller_id = :seller_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["seller_id" => $user_id]);
}

$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Items</title>
</head>
<body>
    <h1>My Items</h1>

    <?php if (count($items) > 0) { ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Sale Type</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo $item["item_id"]; ?></td>
                    <td><?php echo htmlspecialchars($item["item_name"]); ?></td>
                    <td><?php echo htmlspecialchars($item["item_category"]); ?></td>
                    <td><?php echo htmlspecialchars($item["sale_type"]); ?></td>
                    <td><?php echo $item["price"]; ?> €</td>
                    <td><?php echo ucfirst($item["status"]); ?></td>
                    <td>
                        <a href="edit_item.php?id=<?php echo $item["item_id"]; ?>">Edit</a> |
                        <a href="delete_item.php?id=<?php echo $item["item_id"]; ?>" onclick="return confirm('Delete this item?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No items found.</p>
    <?php } ?>

    <p><a href="add_item.php">Add New Item</a></p>
    <p>
        <a href="<?php
            if ($role == "admin") {
                echo'../../admin/admin_dashboard.php';
            }else {
                echo '../auth/seller_dashboard.php';
    }
    ?>">Back to Dashboard</a></p>

</body>
</html>