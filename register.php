<?php
session_start();

// Connect to 'pos' database 
$conn = new mysqli("localhost", "root", "", "pos");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['newUsername']);
    $email = trim($_POST['email']);
    $password = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['userRole'];

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.html"); // redirect back to form
        exit();
    }

    // Check for existing email or username
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email or Username already exists.";
        header("Location: register.html");
        exit();
    }

    // Hash password and insert into users table
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User registered";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration error: " . $conn->error;
        header("Location: register.html");
        exit();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for POS System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: rgb(2, 116, 21);
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color:rgb(17, 150, 39);
        }

        .message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            margin-top: 15px;
        }
        a{
             color: rgb(2, 116, 21);
        }
        a:hover {
             color: rgb(7, 172, 34);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Register New Account</h2>
        <form id="registerForm" method="POST" action="register.php">
            <div class="form-group">
                <label for="newUsername">Username:</label>
                <input type="text" id="newUsername" name="newUsername" placeholder="Choose Username" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter Email Address" required>
            </div>
            <div class="form-group">
                <label for="newPassword">Password:</label>
                <input type="password" id="newPassword" name="newPassword" placeholder="Choose Password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
            </div>
            <div class="form-group">
                <label for="userRole">Role:</label>
                <select id="userRole" name="userRole" required>
                    <option value="" disabled selected> Role</option>
                    <option value="admin">Admin</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>
            <p class="message" id="registerMessage"></p>
            <button type="submit">Register</button>
        </form>
        <p >Already have an account? <a href="login.html">Login here</a></p>
    </div>

  
</body>
</html>
