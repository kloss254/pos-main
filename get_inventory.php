<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 5;

$sql = "SELECT product_name, price, stock FROM products WHERE stock <= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $threshold);
$stmt->execute();

$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>
