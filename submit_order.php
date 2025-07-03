<?php
$conn = new mysqli("localhost", "root", "", "pos");
$data = json_decode(file_get_contents("php://input"), true);

foreach ($data['cart'] as $item) {
    $stmt = $conn->prepare("INSERT INTO orders (product_id, customer_name, customer_phone, quantity, payment_method, user_id, discounts) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississi",
        $item['product_id'],
        $data['customer_name'],
        $data['customer_phone'],
        $item['quantity'],
        $data['payment_method'],
        $data['user_id'],
        $item['discount']
    );
    $stmt->execute();
}
echo json_encode(["status" => "success"]);
?>
