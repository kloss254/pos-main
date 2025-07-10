<?php
// Connect
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId <= 0) {
    die("Invalid order ID.");
}

// Fetch order
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderQuery->bind_param("i", $orderId);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();
$order = $orderResult->fetch_assoc();
if (!$order) {
    die("Order not found.");
}

// Fetch items
$itemQuery = $conn->prepare("
    SELECT oi.*, p.product_name,p.price,p.tax
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemQuery->bind_param("i", $orderId);
$itemQuery->execute();
$itemsResult = $itemQuery->get_result();

// Prepare totals
$subtotal = 0;
$totalTax = 0;
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $lineSubtotal = ($row['price'] * $row['quantity']) - $row['discount'];
    $lineTax = $row['tax'] * $row['quantity'];

    $subtotal += $lineSubtotal;
    $totalTax += $lineTax;

    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Receipt #<?php echo $orderId; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .receipt-container { max-width: 600px; margin: auto; border: 1px solid #ccc; padding: 20px; }
    h2, h3 { text-align: center; margin: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #2d89ef; color: white; }
    .totals { margin-top: 20px; text-align: right; }
    .totals div { margin: 5px 0; }
    .footer { text-align: center; margin-top: 30px; font-size: 0.9em; color: #777; }
    @media print {
        button { display: none; }
    }
    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 20px;
    }
    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="receipt-container">
    <h2>POS Store Receipt</h2>
    <h3>Order #<?php echo $orderId; ?></h3>
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Discount</th>
          <th>Tax</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>KES <?php echo number_format($item['price'], 2); ?></td>
            <td>KES <?php echo number_format($item['discount'], 2); ?></td>
            <td>KES <?php echo number_format($item['tax'] * $item['quantity'], 2); ?></td>
            <td>KES <?php echo number_format(($item['price'] * $item['quantity']) - $item['discount'], 2); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="totals">
      <div><strong>Subtotal:</strong> KES <?php echo number_format($subtotal, 2); ?></div>
      <div><strong>Total Tax:</strong> KES <?php echo number_format($totalTax, 2); ?></div>
      <div><strong>Grand Total:</strong> KES <?php echo number_format($subtotal + $totalTax, 2); ?></div>
    </div>

    <div class="footer">
      Thank you for shopping with us!
    </div>
    <button onclick="window.print()">Print</button>
  </div>
</body>
</html>
