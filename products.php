<?php
$conn = new mysqli("localhost", "root", "", "pos");
$result = $conn->query("SELECT * FROM products");

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
?>
