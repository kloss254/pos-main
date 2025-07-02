<?php
$servername = "localhost";  // Change if using a remote database
$username = "root";         // Default WAMP username
$password = "";             // Default WAMP password (empty by default)
$dbname = "pos";         // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
