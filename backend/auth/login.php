<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $password =($_POST["password"] ?? "");

    if (empty($login) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $sql = "SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["email" => $login,
                        "username" => $login
        ]);
        $user = $stmt->fetch();

        //var_dump($user);
        //die();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["first_name"] = $user["first_name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["email"] = $user["email"];

            if ($user["role"] === "admin") {
                header("Location: ../../index.php");
                exit;
            } elseif ($user["role"] === "seller") {
                header("Location: ../../index.php");
                exit;
            } else {
                header("Location: ../../index.php");
                exit;
            }
        } else {
            $error = "Invalid email/username or password.";
        }
    }
}


?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Login</title>
        <link rel="stylesheet" href="../../style.css">
        </head>
        
        <body class="login-page">
        
          <!--Back Button -->
            <div class="back-button-box login-back">
                
                  <button class="back-btn" onclick="goBack()">← Back</button>
                    </div>
                    
                      <!--Login Box -->
                        <div class="login-container">
                        
                            <div class="logo-wrapper">
                                
                                <img src="/Omnes-marketplace-main/assets/images/logo.png" class="logo-img">
                            </div>

                            <?php if (!empty($error)): ?>
    <p style="color:red; text-align:center;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>
                            <form method="POST" action="" style="display:contents;">
    <input type="text" name="login" placeholder="Username or Email" id="username" required>
    <input type="password" name="password" placeholder="Password" id="password" required>
    <a href="#" class="forgot-link">Forgot password</a>
    <button type="submit" id="login-btn">Log in</button>
    </form>
                            
                                
                                                
                                                    <p class="signup-text">
                                                          Don't have an account? <a href="register.php">Sign up</a>
                                                              </p>
                                                              
                                                                </div>
                                                                
                                                                  <script>
                                                                  
                                                                      // back last page
                                                                          function goBack(){
                                                                                window.history.back();
                                                                                    }
                                                                                    
                                                                                        const loginBtn = document.getElementById("login-btn");
                                                                                        
                                                                                            loginBtn.onclick = function(){
                                                                                                  const username = document.getElementById("username").value;
                                                                                                        const password = document.getElementById("password").value;
                                                                                                        
                                                                                                              if(username === "" || password === ""){
                                                                                                                      alert("Please fill in all fields.");
                                                                                                                            }else{
                                                                                                                                    alert("Login successful!");
                                                                                                                                            window.location.href = "../../index.php";
                                                                                                                                                  }
                                                                                                                                                      };
                                                                                                                                                      
                                                                                                                                                        </script>
                                                                                                                                                        
                                                                                                                                                        </body>
                                                                                                                                                        </html>



                                    