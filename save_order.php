<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

// Read the POST JSON data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    echo json_encode(["success" => false, "message" => "Invalid order data"]);
    exit;
}

$customerName = $conn->real_escape_string($data['customer_name']);
$customerPhone = $conn->real_escape_string($data['customer_phone']);
$total = floatval($data['total']);

// Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, total_amount, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("ssd", $customerName, $customerPhone, $total);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to save order"]);
    exit;
}

$orderId = $stmt->insert_id;
$stmt->close();

// Prepare insert for order_items
$itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount) VALUES (?, ?, ?, ?, ?)");

foreach ($data['cart'] as $item) {
    $productId = intval($item['product_id']);
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);
    $discount = floatval(isset($item['discount']) ? $item['discount'] : 0);

    // Save each order item
    $itemStmt->bind_param("iiidd", $orderId, $productId, $quantity, $price, $discount);
    $itemStmt->execute();

    // Auto-update stock
    $updateStockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $updateStockStmt->bind_param("ii", $quantity, $productId);
    $updateStockStmt->execute();
    $updateStockStmt->close();
}

$itemStmt->close();
$conn->close();

echo json_encode(["success" => true, "message" => "Order saved successfully"]);
