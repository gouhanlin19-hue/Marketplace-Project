<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

// buyer only
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
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

            $insert_sql = "INSERT INTO users (first_name, last_name, email, username, password_hash, role)
                           VALUES (:first_name, :last_name, :email, :username, :password_hash, 'buyer')";

            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email" => $email,
                "username" => $username,
                "password_hash" => $password_hash
            ]);

            // Get new user ID
            $new_user_id = $pdo->lastInsertId();

            // Create cart for this user
            $cart_sql = "INSERT INTO carts (buyer_id) VALUES (:buyer_id)";
            $cart_stmt = $pdo->prepare($cart_sql);
            $cart_stmt->execute(["buyer_id" => $new_user_id]);


            $_SESSION['user_id']    = $new_user_id;
            $_SESSION['role']       = 'buyer';
            $_SESSION['first_name'] = $first_name;
            $_SESSION['username']   = $username;
            $_SESSION['email']      = $email;
            header('Location: ../../index.php');
            exit;
                    }
                }
            }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../../style.css">
</head>

<body class="login-page">

    <div class="back-button-box login-back">
        <button class="back-btn" onclick="goBack()">← Back</button>
    </div>

    <div class="signup-container">
    <div class="logo-wrapper">
         <img src="/Omnes-marketplace-main/assets/images/logo.png" class="logo-img">
    </div>

    <form method="POST" action="" style="display:contents;">

    <?php if (!empty($error)): ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color:green; text-align:center;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <input type="text" name="last_name" placeholder="Last name" required>
    <input type="text" name="first_name" placeholder="First name" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>

    <a href="#" class="forgot">Forgot Password</a>

    <button type="submit" class="signup-btn">Sign up</button>

    </form>


    <p class="login-link-text">
      Do you have an account?
      <a href="login.php">Log in</a>
    </p>
  </div>

  <script>
    function goBack() {
        window.history.back();
    }
  </script>

</body>
</html>

