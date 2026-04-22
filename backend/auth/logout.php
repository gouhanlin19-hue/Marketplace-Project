<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="logo-wrapper">                      
            <img src="/Omnes-marketplace-main/assets/images/logo.png" class="logo-img">
        </div>
        <h2 style="text-align:center;">You have been logged out.</h2>
        <p style="text-align:center; color:var(--text-dark);">See you next time!</p>
        <a href="../../index.php" style="text-align:center;">
            <button class="signup-btn" style="width:100%;">Back to Home</button>
        </a>
        <a href="login.php" style="text-align:center;">
            <button id="login-btn" style="width:100%;">Log in again</button>
        </a>
    </div>

</body>
</html>
