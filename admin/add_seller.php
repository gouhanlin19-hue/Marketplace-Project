<?php
session_start();
require_once __DIR__ . '/../db_connect.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Seller</title>
</head>
<body>
    <h1>Add Seller</h1>

    <?php if ($error != "") { ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>

    <?php if ($success != "") { ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php } ?>

    <form method="POST">
        <label>First Name:</label><br>
        <input type="text" name="first_name"><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Username:</label><br>
        <input type="text" name="username"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit">Create Seller</button>
    </form>

    <p><a href="admin_dashboard.php">Back to dashboard</a></p>
</body>
</html>