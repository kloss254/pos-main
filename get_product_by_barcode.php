<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "pos");

if ($conn->connect_error) {
    die(json_encode(["error" => "DB Connection Failed"]));
}

// Get the barcode from the request
$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    echo json_encode(["error" => "Barcode is required"]);
    exit;
}

// Search for the product
$stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($product = $result->fetch_assoc()) {
    echo json_encode($product);
} else {
    echo json_encode(["error" => "Product not found"]);
}
?>
