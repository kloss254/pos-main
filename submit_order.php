<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Handle invalid JSON
if (is_null($data)) {
    echo json_encode(["status" => "fail", "message" => "Invalid JSON or no data received", "raw_input" => $raw]);
    exit;
}

// Validate required fields
if (
    !isset($data['customer_name']) ||
    !isset($data['customer_phone']) ||
    !isset($data['payment_method']) ||
    !isset($data['user_id']) ||
    !isset($data['cart']) ||
    !is_array($data['cart'])
) {
    echo json_encode(["status" => "fail", "message" => "Missing required fields"]);
    exit;
}

$customer_name = $data['customer_name'];
$customer_phone = $data['customer_phone'];
$payment_method = $data['payment_method'];
$user_id = $data['user_id'];
$cart = $data['cart'];

// DB connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    echo json_encode(["status" => "fail", "message" => "DB connection failed"]);
    exit;
}

// Insert into orders
$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, payment_method, user_id, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("sssi", $customer_name, $customer_phone, $payment_method, $user_id);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;

    // Insert items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount) VALUES (?, ?, ?, ?, ?)");

    foreach ($cart as $item) {
        $product_id = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        $price = (float)$item['price'];
        $discount = (float)$item['discount'];

        $item_stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $price, $discount);
        $item_stmt->execute();
    }

    echo json_encode(["status" => "success", "order_id" => $order_id]);
} else {
    echo json_encode(["status" => "fail", "message" => "Order creation failed"]);
}

$conn->close();
