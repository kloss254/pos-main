<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-box">
        <h4 class="text-center mb-3">Login to POS</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="login_process.php">
            <div class="mb-3">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" id="passwordField" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" onclick="togglePassword()"> Show Password
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById("passwordField");
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
