<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die(json_encode(["error" => "DB Connection failed."]));
}

$result = $conn->query("SELECT DISTINCT category FROM products");
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category'];
}

echo json_encode($categories);
?>
