<?php
header('Content-Type: application/json');

// 1. Connect to database
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

// 2. Get JSON input from frontend
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// 3. Validate input
if (
    empty($data['customer_name']) ||
    empty($data['customer_phone']) ||
    empty($data['payment_method']) ||
    empty($data['user_id']) ||
    empty($data['cart']) ||
    !is_array($data['cart'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid or missing input."]);
    exit;
}

// Sanitize / assign
$customer_name = $conn->real_escape_string($data['customer_name']);
$customer_phone = $conn->real_escape_string($data['customer_phone']);
$payment_method = $conn->real_escape_string($data['payment_method']);
$user_id = (int)$data['user_id'];
$cart = $data['cart'];

// 4. Insert into orders table
$order_stmt = $conn->prepare("
    INSERT INTO orders (customer_name, customer_phone, payment_method, user_id)
    VALUES (?, ?, ?, ?)
");
$order_stmt->bind_param("sssi", $customer_name, $customer_phone, $payment_method, $user_id);

if (!$order_stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to create order."]);
    exit;
}

$order_id = $order_stmt->insert_id;

// 5. Insert cart items into order_items table
$item_stmt = $conn->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, discount, price, tax)
    VALUES (?, ?, ?, ?, ?, ?)
");

foreach ($cart as $item) {
    $product_id = (int)$item['product_id'];
    $quantity = (int)$item['quantity'];
    $discount = (int)$item['discount'];
    $price = (int)$item['price'];
    $tax = (int)$item['tax'];

    // Insert item
    $item_stmt->bind_param("iiiiii", $order_id, $product_id, $quantity, $discount, $price, $tax);
    if (!$item_stmt->execute()) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to insert order item."]);
        exit;
    }

    // 6. Reduce stock in products table
    $stock_update = $conn->prepare("
        UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?
    ");
    $stock_update->bind_param("iii", $quantity, $product_id, $quantity);
    $stock_update->execute();

    if ($stock_update->affected_rows === 0) {
        // Rollback scenario: not enough stock
        // Delete all inserted items and order
        $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
        $conn->query("DELETE FROM orders WHERE order_id = $order_id");

        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Insufficient stock for product ID: $product_id"
        ]);
        exit;
    }
}

// 7. Success response
echo json_encode([
    "status" => "success",
    "message" => "Order completed successfully.",
    "order_id" => $order_id
]);
?>
