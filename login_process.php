<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle POST login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role']));

            // Redirect based on role
            if ($_SESSION['role'] === "admin") {
                header("Location: admin-dashboard.php");
            } elseif ($_SESSION['role'] === "cashier") {
                header("Location: cashier-dashboard.php");
            } else {
                $_SESSION['error'] = "Unauthorized role detected.";
                header("Location: login.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $_SESSION['error'] = "Username not found.";
    }

    header("Location: login.php");
    exit();
}
?>
