<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$product_id = (int)$_POST['product_id'];
$customer_name = $conn->real_escape_string($_POST['customer_name']);
$customer_phone = $conn->real_escape_string($_POST['customer_phone']);
$quantity = (int)$_POST['quantity'];
$payment_method = $conn->real_escape_string($_POST['payment_method']);
$user_id = (int)$_POST['user_id'];
$discounts = isset($_POST['discounts']) ? (float)$_POST['discounts'] : 0.0;

// Get product price and tax
$product_query = "SELECT price, tax FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Product not found.");
}

$product = $result->fetch_assoc();
$price = $product['price']; // per item
$tax_rate = $product['tax']; // e.g. 0.16 = 16%

// Calculate totals
$subtotal = $price * $quantity;
$total_tax = $subtotal * $tax_rate;
$total_amount = $subtotal + $total_tax - $discounts;

// Insert into orders table
$order_query = "
    INSERT INTO orders (
        product_id, customer_name, customer_phone, quantity,
        total_amount, total_tax, payment_method, user_id, discounts, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
";

$stmt = $conn->prepare($order_query);
$stmt->bind_param(
    "sssiddsdi",
    $product_id,
    $customer_name,
    $customer_phone,
    $quantity,
    $total_amount,
    $total_tax,
    $payment_method,
    $user_id,
    $discounts
);

if ($stmt->execute()) {
    echo "<p>✅ Order placed successfully.</p>";
    echo "<p><a href='order_form.php'>Place another order</a></p>";
} else {
    echo "<p>❌ Error: " . $stmt->error . "</p>";
}

$conn->close();
?>
