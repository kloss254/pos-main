<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchTerm = $_GET['search'] ?? '';

$sql = "
  SELECT 
    o.order_id, o.customer_name, o.customer_phone, o.payment_method, o.status, o.created_at,
    oi.product_id, oi.quantity, oi.discount, oi.price AS line_price,
    p.product_name
  FROM orders o
  JOIN order_items oi ON oi.order_id = o.order_id
  JOIN products p ON p.id = oi.product_id
  WHERE o.status = 'delivered'
";

if (!empty($searchTerm)) {
    $searchTermEscaped = "%" . $conn->real_escape_string($searchTerm) . "%";
    $sql .= " AND (o.customer_name LIKE '$searchTermEscaped' OR o.customer_phone LIKE '$searchTermEscaped')";
}

$sql .= " ORDER BY o.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales Report - Delivered Orders</title>
  <link rel="stylesheet" href="cashier-styles.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
    .sidebar-link {
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
    .sidebar-link.active {
      background-color: #34495e;
    }
    .sidebar-link:hover {
      background-color: #0056b3;
    }
    .main-content {
      margin-left: 250px;
      padding: 20px;
      overflow-y: auto;
      height: 100vh;
      width: calc(100% - 250px);
      background-color: #f4f4f4;
    }
    h1 {
      margin-top: 0;
    }
    .search-bar {
      margin: 15px 0;
      display: flex;
      gap: 10px;
    }
    .search-bar input[type="text"] {
      padding: 10px;
      width: 300px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .search-bar button {
      padding: 10px 15px;
      background-color: #2d89ef;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
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
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .btn-pdf {
      padding: 5px 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
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
          <li><a href="cashier-dashboard.php" class="sidebar-link"><i class="fas fa-box"></i> Products</a></li>
          <li><a href="cashier-orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> Orders</a></li>
          <li><a href="cashier-sales.php" class="sidebar-link active"><i class="fas fa-cash-register"></i> Sales</a></li>
          <li><a href="accounting.php" class="sidebar-link"><i class="fas fa-calculator"></i> Accounting</a></li>
          <li><a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
      </nav>
    </div>
    <a href="#" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="main-content">
    <h1>Sales Report - Delivered Orders</h1>

    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by customer name or phone..." value="<?= htmlspecialchars($searchTerm) ?>" />
      <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

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
          <th>Receipt</th>
        </tr>
      </thead>
      <tbody>
<?php
$items = [];
$totals = [];

while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    $lineSubtotal = ($row['line_price'] * $row['quantity']) - $row['discount'];

    if (!isset($items[$orderId])) {
        $items[$orderId] = [];
        $totals[$orderId] = 0;
    }

    $items[$orderId][] = $row;
    $totals[$orderId] += $lineSubtotal;
}

foreach ($items as $orderId => $orderLines):
    $first = $orderLines[0];
    $rowspan = count($orderLines);
?>
<tr>
  <td rowspan="<?= $rowspan ?>"><?= $orderId ?></td>
  <td><?= htmlspecialchars($first['product_name']) ?> (x<?= $first['quantity'] ?>)</td>
  <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($first['customer_name']) ?></td>
  <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($first['customer_phone']) ?></td>
  <td><?= $first['quantity'] ?></td>
  <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($first['payment_method']) ?></td>
  <td><?= number_format($first['discount'], 2) ?></td>
  <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($first['created_at']) ?></td>
  <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($first['status']) ?></td>
  <td rowspan="<?= $rowspan ?>">KES <?= number_format($totals[$orderId], 2) ?></td>
  <td rowspan="<?= $rowspan ?>">
    <a href="print_receipt.php?order_id=<?= $orderId ?>" target="_blank" class="btn-pdf">Receipt</a>
  </td>
</tr>
<?php for ($i = 1; $i < count($orderLines); $i++): 
  $line = $orderLines[$i]; ?>
<tr>
  <td><?= htmlspecialchars($line['product_name']) ?> (x<?= $line['quantity'] ?>)</td>
  <td><?= $line['quantity'] ?></td>
  <td><?= number_format($line['discount'], 2) ?></td>
</tr>
<?php endfor; endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
