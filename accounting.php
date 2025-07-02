<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$whereClause = '';
$params = [];
$types = '';

if ($startDate && $endDate) {
    $whereClause = "AND DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types = 'ss';
}

// SALES SUMMARY
$salesQuery = "
    SELECT 
        SUM((oi.price * oi.quantity) - oi.discount) AS total_revenue,
        SUM(oi.tax * oi.quantity) AS total_tax,
        SUM(oi.discount) AS total_discounts,
        COUNT(DISTINCT o.order_id) AS total_orders
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.status = 'delivered' $whereClause
";

$salesStmt = $conn->prepare($salesQuery);
if ($whereClause) $salesStmt->bind_param($types, ...$params);
$salesStmt->execute();
$sales = $salesStmt->get_result()->fetch_assoc();

$totalRevenue = $sales['total_revenue'] ?? 0;
$totalTax = $sales['total_tax'] ?? 0;
$totalDiscounts = $sales['total_discounts'] ?? 0;
$totalOrders = $sales['total_orders'] ?? 0;

// PURCHASES SUMMARY
$purchasesWhere = '';
$purchasesParams = [];
$purchasesTypes = '';

if ($startDate && $endDate) {
    $purchasesWhere = "WHERE invoice_date BETWEEN ? AND ?";
    $purchasesParams[] = $startDate;
    $purchasesParams[] = $endDate;
    $purchasesTypes = 'ss';
}

$purchasesStmt = $conn->prepare("SELECT SUM(total_amount) AS total_purchases FROM supply_invoices $purchasesWhere");
if ($purchasesWhere) $purchasesStmt->bind_param($purchasesTypes, ...$purchasesParams);
$purchasesStmt->execute();
$purchases = $purchasesStmt->get_result()->fetch_assoc();
$totalPurchases = $purchases['total_purchases'] ?? 0;

// SIMPLE PROFIT
$grossProfit = $totalRevenue - $totalPurchases;

// DELIVERED ORDERS TABLE
$orderQuery = "
    SELECT o.order_id, o.customer_name, o.customer_phone, o.payment_method, o.created_at,
           oi.product_id, oi.quantity, oi.discount, oi.price, oi.tax,
           p.product_name
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'delivered' $whereClause
    ORDER BY o.created_at DESC
";
$orderStmt = $conn->prepare($orderQuery);
if ($whereClause) $orderStmt->bind_param($types, ...$params);
$orderStmt->execute();
$deliveredOrders = $orderStmt->get_result();

// SUPPLY INVOICES
$supplyStmt = $conn->prepare("
    SELECT si.*, s.supplier_name 
    FROM supply_invoices si 
    LEFT JOIN suppliers s ON si.supplier_id = s.id
    $purchasesWhere
    ORDER BY si.invoice_date DESC
");
if ($purchasesWhere) $supplyStmt->bind_param($purchasesTypes, ...$purchasesParams);
$supplyStmt->execute();
$supplyInvoices = $supplyStmt->get_result();

// INVENTORY LOGS
$inventoryLogs = $conn->query("
    SELECT il.*, p.product_name 
    FROM inventory_logs il 
    LEFT JOIN products p ON il.product_id = p.id 
    ORDER BY il.timestamp DESC 
    LIMIT 50
");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>POS Accounting Dashboard</title>
  <link rel="stylesheet" href="cashier-styles.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body { margin:0; font-family: Arial; background:#f4f4f4; }
    .main-content { margin-left:250px; padding:20px; }
    h1, h2 { color:#333; }
    .cards { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px; }
    .card { background:white; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); flex:1; min-width:200px; }
    .card h3 { margin:0; font-size:16px; color:#666; }
    .card p { font-size:22px; margin:8px 0 0; font-weight:bold; }
    table { width:100%; border-collapse:collapse; background:white; margin-bottom:30px; min-width:900px; }
    th, td { padding:10px; border:1px solid #ddd; text-align:left; }
    th { background:#2d89ef; color:white; }
    tr:nth-child(even) { background:#f9f9f9; }
    form.filters { margin-bottom:20px; background:white; padding:15px; box-shadow:0 2px 5px rgba(0,0,0,0.1); border-radius:8px; }
    form.filters input { padding:8px; margin-right:10px; }
    form.filters button { padding:8px 16px; background:#28a745; color:white; border:none; border-radius:4px; cursor:pointer; }
    .export-links { margin-bottom:20px; }
    .export-links a { text-decoration:none; background:#007bff; color:white; padding:8px 16px; border-radius:4px; margin-right:10px; }
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
.cashier-sidebar .sidebar-menu ul li a.active i {
    color: white; /* White icon for active */
}

.cashier-sidebar .sidebar-menu ul li a:hover:not(.active) {
    background-color: #34495e; /* Light blue hover */
    color: #fff; /* Lighter text color on hover */
}
body {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
  </style>
</head>
<body>
    <div id="cashier-app">
        <aside class="cashier-sidebar">
            <div class="logo">
                <img src="logo.png" alt="POS Logo"> <span>POS</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                <li><a href="cashier-dashboard.php" class="sidebar-link "><i class="fas fa-box"></i> Products</a></li>
                <li><a href="cashier-orders.php" class="sidebar-link " ><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="cashier-sales.php" class="sidebar-link"  ><i class="fas fa-cash-register"></i> Sales</a></li>
                <li><a href="accounting.php" class="sidebar-link  active" ><i class="fas fa-calculator"></i> Accounting</a></li>
                <li><a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </nav>
            <div class="user-cards">
                <div class="user-card active">
                    <span class="initials">DJ</span>
                    <div class="user-details">
                        <span class="name">Dilys Joy</span>
                        <span class="role">Cashier</span>
                    </div>
                </div>
                <div class="user-card">
                    <span class="initials">FLO</span>
                    <div class="user-details">
                        <span class="name">Florence</span>
                        <span class="role">Cashier</span>
                    </div>
                </div>
            </div>
            <a href="#" onclick="logout()" class="sidebar-logout" id="cashier-logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </aside>


<main class="main-content">
  <h1>Accounting Dashboard</h1>

  <form method="GET" class="filters">
    <label>Start Date:</label>
    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
    <label>End Date:</label>
    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
    <button type="submit">Apply Filter</button>
  </form>

  <div class="export-links">
    <a href="export-accounting.php?type=pdf&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"> Export PDF</a>
    <a href="export-accounting.php?type=excel&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"> Export Excel</a>
  </div>

  <div class="cards">
    <div class="card"><h3>Total Revenue</h3><p>KES <?= number_format($totalRevenue, 2) ?></p></div>
    <div class="card"><h3>Total Tax Collected</h3><p>KES <?= number_format($totalTax, 2) ?></p></div>
    <div class="card"><h3>Total Discounts Given</h3><p>KES <?= number_format($totalDiscounts, 2) ?></p></div>
    <div class="card"><h3>Delivered Orders</h3><p><?= $totalOrders ?></p></div>
    <div class="card"><h3>Total Purchases</h3><p>KES <?= number_format($totalPurchases, 2) ?></p></div>
    <div class="card"><h3>Gross Profit</h3><p>KES <?= number_format($grossProfit, 2) ?></p></div>
  </div>

  <h2>Delivered Orders</h2>
  <table>
    <thead>
      <tr><th>ID</th><th>Product</th><th>Customer</th><th>Qty</th><th>Discount</th><th>Price</th><th>Tax</th><th>Total</th><th>Payment</th><th>Date</th></tr>
    </thead>
    <tbody>
    <?php while($row = $deliveredOrders->fetch_assoc()): 
      $subtotal = ($row['quantity'] - $row['discount']) * $row['price']; ?>
      <tr>
        <td><?= $row['order_id'] ?></td>
        <td><?= $row['product_name'] ?></td>
        <td><?= $row['customer_name'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= $row['discount'] ?></td>
        <td><?= number_format($row['price'], 2) ?></td>
        <td><?= number_format($row['quantity'] * $row['tax'], 2) ?></td>
        <td><?= number_format($subtotal, 2) ?></td>
        <td><?= $row['payment_method'] ?></td>
        <td><?= $row['created_at'] ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <h2>Supply Invoices</h2>
  <table>
    <thead><tr><th>Invoice #</th><th>Supplier</th><th>Date</th><th>Total Amount</th></tr></thead>
    <tbody>
    <?php while($inv = $supplyInvoices->fetch_assoc()): ?>
      <tr>
        <td><?= $inv['invoice_number'] ?></td>
        <td><?= $inv['supplier_name'] ?? 'Unknown' ?></td>
        <td><?= $inv['invoice_date'] ?></td>
        <td>KES <?= number_format($inv['total_amount'], 2) ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

  <h2>Recent Inventory Logs</h2>
  <table>
    <thead><tr><th>Product</th><th>Action</th><th>Qty</th><th>Old Stock</th><th>New Stock</th><th>User</th><th>Date</th></tr></thead>
    <tbody>
    <?php while($log = $inventoryLogs->fetch_assoc()): ?>
      <tr>
        <td><?= $log['product_name'] ?></td>
        <td><?= $log['action'] ?></td>
        <td><?= $log['quantity'] ?></td>
        <td><?= $log['old_stock'] ?></td>
        <td><?= $log['new_stock'] ?></td>
        <td><?= $log['user'] ?></td>
        <td><?= $log['timestamp'] ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</main>
</body>
</html>
