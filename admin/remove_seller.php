<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// DELETE seller
if (isset($_GET["id"])) {
    $user_id = $_GET["id"];

    $delete_sql = "DELETE FROM users WHERE user_id = :user_id AND role = 'seller'";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute(["user_id" => $user_id]);

    header("Location: remove_seller.php");
    exit;
}

// GET sellers
$sql = "SELECT user_id, first_name, last_name, email FROM users WHERE role = 'seller'";
$stmt = $pdo->query($sql);
$sellers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Seller</title>
</head>
<body>
    <h1>Manage Sellers</h1>

    <?php if (count($sellers) > 0) { ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>

            <?php foreach ($sellers as $seller) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($seller["first_name"] . " " . $seller["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($seller["email"]); ?></td>
                    <td>
                        <a href="remove_seller.php?id=<?php echo $seller["user_id"]; ?>" onclick="return confirm('Delete this seller?')">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No sellers found.</p>
    <?php } ?>

    <p><a href="admin_dashboard.php">Back to dashboard</a></p>
</body>
</html>