<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update order status (Deliver/Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);

    if ($new_status === 'delivered') {
        // Reduce stock for all items in this order
        $result = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
        while ($row = $result->fetch_assoc()) {
            $product_id = (int)$row['product_id'];
            $quantity = (int)$row['quantity'];
            $conn->query("UPDATE products SET stock = GREATEST(stock - $quantity, 0) WHERE id = $product_id");
        }
    }

    $conn->query("UPDATE orders SET status = '$new_status' WHERE order_id = $order_id");
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function fetchOrdersByStatus($status, $conn) {
    $stmt = $conn->prepare("
        SELECT 
            o.order_id, o.customer_name, o.customer_phone, o.payment_method, o.status, o.created_at,
            oi.product_id, oi.quantity, oi.discount, oi.price AS line_price, oi.tax AS line_tax,
            p.product_name
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.status = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result();
}

function renderOrdersTable($orders, $showActions = false) {
    $items = [];
    $totals = [];

    while ($row = $orders->fetch_assoc()) {
        $orderId = $row['order_id'];
        $lineSubtotal = ($row['line_price'] * $row['quantity']) - $row['discount'];
        $lineTax = $row['line_tax'] * $row['quantity'];

        if (!isset($items[$orderId])) {
            $items[$orderId] = [];
            $totals[$orderId] = ['subtotal' => 0, 'tax' => 0];
        }

        $items[$orderId][] = $row;
        $totals[$orderId]['subtotal'] += $lineSubtotal;
        $totals[$orderId]['tax'] += $lineTax;
    }

    foreach ($items as $orderId => $orderLines) {
        $first = $orderLines[0];
        $rowspan = count($orderLines);
        echo "<tr>";
        echo "<td rowspan='$rowspan'>$orderId</td>";
        echo "<td>{$first['product_name']} (x{$first['quantity']})</td>";
        echo "<td rowspan='$rowspan'>{$first['customer_name']}</td>";
        echo "<td rowspan='$rowspan'>{$first['customer_phone']}</td>";
        echo "<td>{$first['quantity']}</td>";
        echo "<td rowspan='$rowspan'>{$first['payment_method']}</td>";
        echo "<td>{$first['discount']}</td>";
        echo "<td rowspan='$rowspan'>{$first['created_at']}</td>";
        echo "<td rowspan='$rowspan'>{$first['status']}</td>";
        echo "<td rowspan='$rowspan'>KES " . number_format($totals[$orderId]['subtotal'], 2) . "</td>";
        echo "<td rowspan='$rowspan'>KES " . number_format($totals[$orderId]['tax'], 2) . "</td>";

        echo "<td rowspan='$rowspan'>";
        if ($showActions) {
            echo "<form method='POST' style='display:inline;'>
                    <input type='hidden' name='order_id' value='$orderId'>
                    <button name='new_status' value='delivered' class='btn-deliver'>Deliver</button>
                  </form>
                  <form method='POST' style='display:inline;'>
                    <input type='hidden' name='order_id' value='$orderId'>
                    <button name='new_status' value='cancelled' class='btn-cancel'>Cancel</button>
                  </form>";
        }
        if ($first['status'] === 'delivered') {
    echo "<button onclick='printReceipt($orderId)' class='btn-print'>Print Receipt</button>";
}
        echo "</td></tr>";

        for ($i = 1; $i < count($orderLines); $i++) {
            $line = $orderLines[$i];
            echo "<tr>
                    <td>{$line['product_name']} (x{$line['quantity']})</td>
                    <td>{$line['quantity']}</td>
                    <td>{$line['discount']}</td>
                  </tr>";
        }
    }
}

$pendingOrders = fetchOrdersByStatus('pending', $conn);
$deliveredOrders = fetchOrdersByStatus('delivered', $conn);
$cancelledOrders = fetchOrdersByStatus('cancelled', $conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Status Management</title>
  <link rel="stylesheet" href="cashier-styles.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
        * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }
    .cashier-sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: white;
      padding: 20px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
    }
    .sidebar-menu ul { list-style: none; padding: 0; }
    .sidebar-menu ul li { margin-bottom: 10px; }
    .sidebar-link:hover { background-color: #0056b3; }
    .user-cards { margin-top: auto; }
    .user-card { margin-bottom: 10px; }
    .main-content {
      margin-left: 250px;
      padding: 20px;
      overflow-y: auto;
      overflow-x: auto;
      height: 100vh;
      width: calc(100% - 250px);
      background-color: #f4f4f4;
    }
    .cashier-sidebar .sidebar-menu ul li a {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px 25px;
      color: #fff;
      font-size: 1.1em;
      font-weight: 500;
      border-radius: 5px;
      transition: all 0.3s ease;
    }
    .cashier-sidebar .sidebar-menu ul li a.active i { color: white; }
    .cashier-sidebar .sidebar-menu ul li a:hover:not(.active) {
      background-color: #34495e;
      color: #fff;
    }
    h1 { margin-top: 0; }
    h2 { margin-top: 40px; color: #333; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      min-width: 1000px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #2d89ef;
      color: #fff;
    }
    tr:nth-child(even) { background-color: #f9f9f9; }
    form button {
      padding: 5px 10px;
      margin-right: 5px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-deliver { background-color: #28a745; color: white; }
    .btn-cancel { background-color: #dc3545; color: white; }
    body {
      font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    .btn-print {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-print:hover {
  background-color: #0056b3;
}

  </style>
</head>
<body>
 <aside class="cashier-sidebar">
    <nav class="sidebar-menu">
      <ul>
        <li><a href="cashier-dashboard.php" class="sidebar-link"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="cashier-orders.php" class="sidebar-link active"><i class="fas fa-receipt"></i> Orders</a></li>
        <li><a href="cashier-sales.php" class="sidebar-link"><i class="fas fa-cash-register"></i> Sales</a></li>
        <li><a href="accounting.php" class="sidebar-link" ><i class="fas fa-calculator"></i> Accounting</a></li>
      </ul>
    </nav>
    <a href="#" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="main-content">
    <h1>POS Order Status Management</h1>

    <h2>Pending Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Product</th><th>Customer Name</th><th>Phone</th>
          <th>Quantity</th><th>Payment</th><th>Discount</th><th>Created At</th>
          <th>Status</th><th>Total Price</th><th>Total Tax</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php renderOrdersTable($pendingOrders, true); ?>
      </tbody>
    </table>

    <h2>Delivered Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Product</th><th>Customer Name</th><th>Phone</th>
          <th>Quantity</th><th>Payment</th><th>Discount</th><th>Created At</th>
          <th>Status</th><th>Total Price</th><th>Total Tax</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php renderOrdersTable($deliveredOrders, true); ?>
      </tbody>
    </table>

    <h2>Cancelled Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Product</th><th>Customer Name</th><th>Phone</th>
          <th>Quantity</th><th>Payment</th><th>Discount</th><th>Created At</th>
          <th>Status</th><th>Total Price</th><th>Total Tax</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php renderOrdersTable($cancelledOrders, true); ?>
      </tbody>
    </table>
  </main>
<script>
  function printReceipt(orderId) {
      window.open('print_receipt.php?order_id=' + orderId, '_blank');
  }
</script>
</body>
</html>
