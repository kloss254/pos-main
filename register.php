<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['newUsername']);
    $email = trim($_POST['email']);
    $password = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['userRole'];

    // Validation
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    if (!preg_match("/^(?=.*[A-Z])(?=.*\W).{8,}$/", $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters with 1 uppercase & 1 special character.";
        header("Location: register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email or username already exists.";
        header("Location: register.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: register.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .auth-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            width: 400px;
        }
        .password-checklist {
            font-size: 14px;
            margin-top: 10px;
        }
        .password-checklist span {
            display: block;
        }
        .valid {
            color: green;
        }
        .invalid {
            color: red;
        }
    </style>
</head>
<body>
<div class="auth-container">
    <h3 class="text-center mb-3">Create Account</h3>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="newUsername" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="newPassword" id="password" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" onclick="togglePassword()"> Show Password
        </div>

        <div class="password-checklist" id="checklist">
            <span id="length" class="invalid">✔ Minimum 8 characters</span>
            <span id="uppercase" class="invalid">✔ At least 1 uppercase letter</span>
            <span id="special" class="invalid">✔ At least 1 special character</span>
        </div>

        <div class="mb-3 mt-3">
            <label>Role</label>
            <select class="form-select" name="userRole" required>
                <option value="" disabled selected>Select Role</option>
                <option value="admin">Admin</option>
                <option value="cashier">Cashier</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100">Register</button>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<script>
    function togglePassword() {
        const fields = [document.getElementById("password"), document.getElementById("confirmPassword")];
        fields.forEach(field => {
            field.type = field.type === "password" ? "text" : "password";
        });
    }

    document.getElementById("password").addEventListener("input", function () {
        const val = this.value;
        document.getElementById("length").className = val.length >= 8 ? "valid" : "invalid";
        document.getElementById("uppercase").className = /[A-Z]/.test(val) ? "valid" : "invalid";
        document.getElementById("special").className = /[\W]/.test(val) ? "valid" : "invalid";
    });
</script>
</body>
</html>
