<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);

    if ($new_status === 'delivered') {
        // Get product_id and quantity for this order
        $result = $conn->query("SELECT product_id, quantity FROM orders WHERE order_id = $order_id");
        if ($result && $result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $product_id = (int)$order['product_id'];
            $quantity = (int)$order['quantity'];

            // Reduce the stock, never going below 0
            $conn->query("UPDATE products SET stock = GREATEST(stock - $quantity, 0) WHERE id = $product_id");
        }
    }

    // Update the order status
    $conn->query("UPDATE orders SET status = '$new_status' WHERE order_id = $order_id");

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function fetchOrdersByStatus($status, $conn) {
    $stmt = $conn->prepare("
        SELECT o.*, p.product_name, p.tax, p.price
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.status = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result();
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
  </style>
</head>
<body>
  <aside class="cashier-sidebar">
    <div>
      <div class="logo">
        <img src="logo.png" alt="POS Logo" style="width:30px; vertical-align:middle; margin-right:8px;">
      </div>
      <nav class="sidebar-menu">
        <ul>
          <li><a href="cashier-dashboard.php" class="sidebar-link "><i class="fas fa-box"></i> Products</a></li>
          <li><a href="#" class="sidebar-link "><i class="fas fa-concierge-bell"></i> Table Services</a></li>
          <li><a href="cashier-orders.php" class="sidebar-link active" ><i class="fas fa-receipt"></i> Orders</a></li>
          <li><a href="cashier-sales.php" class="sidebar-link"><i class="fas fa-cash-register"></i> Sales</a></li>
          <li><a href="#" class="sidebar-link"><i class="fas fa-calculator"></i> Accounting</a></li>
          <li><a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
      </nav>
    </div>
    <a href="#" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="main-content">
    <h1>POS Order Status Management</h1>

    <?php
    function renderOrdersTable($orders, $showActions = false) {
        while ($row = $orders->fetch_assoc()) {
            $quantity = $row['quantity'];
            $price = $row['price'];
            $discount = $row['discounts'];
            $tax = $row['tax'];

            $subtotal = ($quantity - $discount) * $price;
            $totalTax = $quantity * $tax;
            echo "<tr>
                <td>{$row['order_id']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['customer_phone']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['payment_method']}</td>
                <td>{$row['discounts']}</td>
                <td>{$row['created_at']}</td>
                <td>{$row['status']}</td>
                <td>KES " . number_format($subtotal, 2) . "</td>
                <td>KES " . number_format($totalTax, 2) . "</td>";

            if ($showActions) {
                echo "<td>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                        <button name='new_status' value='delivered' class='btn-deliver'>Deliver</button>
                    </form>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                        <button name='new_status' value='cancelled' class='btn-cancel'>Cancel</button>
                    </form>
                </td>";
            }

            echo "</tr>";
        }
    }
    ?>

    <h2>Pending Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>Customer Name</th>
          <th>Phone</th>
          <th>Quantity</th>
          <th>Payment</th>
          <th>Discount</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Total Price</th>
          <th>Total Tax</th>
          <th>Action</th>
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
          <th>ID</th>
          <th>Product</th>
          <th>Customer Name</th>
          <th>Phone</th>
          <th>Quantity</th>
          <th>Payment</th>
          <th>Discount</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Total Price</th>
          <th>Total Tax</th>
        </tr>
      </thead>
      <tbody>
        <?php renderOrdersTable($deliveredOrders); ?>
      </tbody>
    </table>

    <h2>Cancelled Orders</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>Customer Name</th>
          <th>Phone</th>
          <th>Quantity</th>
          <th>Payment</th>
          <th>Discount</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Total Price</th>
          <th>Total Tax</th>
        </tr>
      </thead>
      <tbody>
        <?php renderOrdersTable($cancelledOrders); ?>
      </tbody>
    </table>
  </main>
</body>
</html>
