<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Optional: delete "remember me" cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to login after short delay
header("Refresh: 2; URL=login.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #3498db;
        }
        .logout-message {
            text-align: center;
            font-size: 1.4em;
            background-color: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logout-message i {
            font-size: 2em;
            margin-bottom: 15px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="logout-message">
        <i class="fas fa-sign-out-alt"></i>
        Logging you out... <br>
        Redirecting to login page.
    </div>

    <!-- Font Awesome for icon -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
