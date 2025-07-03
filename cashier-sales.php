<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchTerm = $_GET['search'] ?? '';
$sql = "
  SELECT o.*, p.product_name 
  FROM orders o
  LEFT JOIN products p ON o.product_id = p.id
  WHERE o.status = 'delivered'
";

if (!empty($searchTerm)) {
    $searchTermEscaped = "%" . $conn->real_escape_string($searchTerm) . "%";
    $sql .= " AND (o.customer_name LIKE '$searchTermEscaped' OR o.customer_phone LIKE '$searchTermEscaped')";
}

$sql .= " ORDER BY created_at DESC";
$deliveredOrders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Delivered Orders</title>
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
          <li><a href="#" class="sidebar-link"><i class="fas fa-concierge-bell"></i> Table Services</a></li>
          <li><a href="cashier-orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> Orders</a></li>
          <li><a href="cashier-sales.php" class="sidebar-link active"><i class="fas fa-cash-register"></i> Sales</a></li>
          <li><a href="#" class="sidebar-link"><i class="fas fa-calculator"></i> Accounting</a></li>
          <li><a href="#" class="sidebar-link"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
      </nav>
    </div>
    <a href="#" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="main-content">
    <h1>Delivered Orders</h1>

    <form method="GET" class="search-bar">
      <input type="text" name="search" placeholder="Search by customer name or phone..." value="<?= htmlspecialchars($searchTerm) ?>" />
      <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product Name</th>
          <th>Customer Name</th>
          <th>Phone</th>
          <th>Quantity</th>
          <th>Payment</th>
          <th>Discount</th>
          <th>Created At</th>
          <th>Status</th>
          <th>PDF</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $deliveredOrders->fetch_assoc()): ?>
        <tr>
          <td><?= $row['order_id'] ?></td>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td><?= $row['customer_name'] ?></td>
          <td><?= $row['customer_phone'] ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= $row['payment_method'] ?></td>
          <td><?= $row['discounts'] ?></td>
          <td><?= $row['created_at'] ?></td>
          <td><?= $row['status'] ?></td>
          <td>
            <a href="generate_order_pdf.php?order_id=<?= $row['order_id'] ?>" target="_blank" class="btn-pdf">PDF</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
