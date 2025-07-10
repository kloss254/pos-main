<?php
session_start();

// Restrict access to admin users only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// DB connection
function getConnection() {
    $conn = new mysqli("localhost", "root", "", "pos");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Handle new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_status']) && !isset($_POST['update_payment'])) {
    $conn = getConnection();

    if (
        isset($_POST['id'], $_POST['customer_name'], $_POST['customer_phone'],
              $_POST['quantity'], $_POST['payment_method'], $_POST['user_id'], $_POST['discounts'])
    ) {
        $product_id = (int)$_POST['id'];
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
        $quantity = (int)$_POST['quantity'];
        $payment_method = $conn->real_escape_string($_POST['payment_method']);
        $user_id = (int)$_POST['user_id'];
        $discounts = (int)$_POST['discounts'];

        // Insert into orders
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, payment_method, user_id, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssi", $customer_name, $customer_phone, $payment_method, $user_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert into order_items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, id, quantity, discount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $order_id, $product_id, $quantity, $discounts);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle payment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $conn = getConnection();

    $order_id = (int)$_POST['order_id'];
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    $statusCheck = $conn->query("SELECT status FROM orders WHERE id = $order_id")->fetch_assoc();
    if ($statusCheck && $statusCheck['status'] === 'delivered' && $payment_method === 'Unpaid') {
        echo "<script>alert('Cannot set payment method to Unpaid for a delivered order.');</script>";
    } else {
        $stmt = $conn->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
        $stmt->bind_param("si", $payment_method, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle status update (including stock reduction)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $conn = getConnection();

    $order_id = (int)$_POST['order_id'];
    $new_status = $conn->real_escape_string($_POST['status']);

    $res = $conn->query("SELECT payment_method, status FROM orders WHERE id = $order_id");
    $row = $res->fetch_assoc();
    $payment_method = $row['payment_method'];
    $current_status = $row['status'];

    if ($new_status === 'delivered' && $payment_method === 'Unpaid') {
        echo "<script>alert('Cannot mark as delivered. Payment is unpaid.');</script>";
    } else {
        if ($current_status !== 'delivered' && $new_status === 'delivered') {
            // reduce stock
            $items = $conn->query("SELECT id, quantity FROM order_items WHERE order_id = $order_id");
            while ($item = $items->fetch_assoc()) {
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt->execute();
                $stmt->close();
            }
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS System - Orders Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --primary-color: #3498db;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #1abc9c;
            --dark-color: #34495e;
            --light-color: #ecf0f1;
        }
        
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        #wrapper {
            display: flex;
            min-height: 100%;
        }
        
        /* Sidebar Styles */
        #sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            transition: all 0.3s;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: #1a252f;
        }
        
        .sidebar-header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            padding: 12px 20px;
            display: block;
            color: #bbb;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: #34495e;
            color: white;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .dropdown-menu {
            list-style: none;
            padding-left: 20px;
            background-color: #34495e;
            display: none;
        }
        
        .dropdown-menu li a {
            padding: 10px 15px;
            font-size: 0.9em;
        }
        
        .dropdown.open .dropdown-menu {
            display: block;
        }
        
        .dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        
        .caret-icon {
            transition: transform 0.3s;
        }
        
        .dropdown.open .caret-icon {
            transform: rotate(180deg);
        }
        
        /* Main Content Styles */
        #main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .main-header h2 {
            margin: 0;
            color: #2c3e50;
        }
        
        /* Button Styles */
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .table tr:hover {
            background-color: #f5f5f5;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            color: white;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-danger {
            background-color: var(--danger-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
        }
        
        .badge-info {
            background-color: var(--info-color);
        }
        
        /* Order Form Styles */
        .order-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .order-form-column {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Filter Section */
        .filter-section {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .filter-row .form-control {
            flex: 1 1 200px;
        }
        
        /* Status Colors */
        .status-pending {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .status-delivered {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .status-cancelled {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            #main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .order-form-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-row .form-control {
                flex: 1 1 100%;
            }
        }
        
        /* Product Image */
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Print Receipt Button */
        .print-btn {
            background-color: #9b59b6;
            color: white;
        }
        
        /* Payment Status */
        .payment-paid {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .payment-unpaid {
            color: var(--danger-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <aside id="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-store"></i> POS Dashboard</h1>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin-orders.php" class="active"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="admin-sales.php"><i class="fas fa-cash-register"></i> Sales</a></li>
                <li><a href="admin-products.php"><i class="fas fa-box"></i> Products</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-warehouse"></i> Inventory
                        <i class="fas fa-caret-down caret-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin-inventory.php">Inventory Management</a></li>
                        <li><a href="inventory-report.php">Inventory Report</a></li>
                        <li><a href="inventory-history.php">Inventory Logs</a></li>
                        <li><a href="update_stock.php">Update Stock</a></li>
                        <li><a href="check_low_stock.php">Low Stock Alert</a></li>
                        <li><a href="export_inventory_pdf.php">Export to PDF</a></li>
                        <li><a href="export_inventory_excel.php">Export to Excel</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-truck"></i> Suppliers
                        <i class="fas fa-caret-down caret-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="admin-suppliers.php">Supplier List</a></li>
                        <li><a href="add-supplier.php">Add Supplier</a></li>
                        <li><a href="supply_invoices_list.php">Supply Invoice List</a></li>
                        <li><a href="add_supply_invoice.php">Add Supply Invoice</a></li>
                    </ul>
                </li>
                <li><a href="sales-report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div id="main-content">
            <header class="main-header">
                <h2><i class="fas fa-receipt"></i> Orders Management</h2>
                <div class="header-actions">
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> New Order</button>
                </div>
            </header>

            <main>
                <!-- New Order Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Create New Order</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="order-form-grid">
                                <div class="order-form-column">
                                    <div class="form-group">
                                        <label for="product_id">Product ID:</label>
                                        <input type="number" class="form-control" name="product_id" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_name">Customer Name:</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_phone">Customer Phone:</label>
                                        <input type="text" class="form-control" name="customer_phone" required>
                                    </div>
                                </div>
                                <div class="order-form-column">
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" class="form-control" name="quantity" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method:</label>
                                        <select class="form-control" name="payment_method" required>
                                            <option value="Mpesa">Mpesa</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Unpaid">Unpaid</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="user_id">User ID:</label>
                                        <input type="number" class="form-control" name="user_id" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="discounts">Discounts:</label>
                                        <input type="number" class="form-control" name="discounts" value="0" required>
                                    </div>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Submit Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-filter"></i> Filter Orders</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="filter-section" id="filter-form">
                            <div class="filter-row">
                                <input type="text" class="form-control" name="customer_name" placeholder="Customer Name">
                                <input type="text" class="form-control" name="product_name" placeholder="Product Name">
                                <input type="text" class="form-control" name="user_id" placeholder="User ID">
                                <select class="form-control" name="payment_method">
                                    <option value="">All Payments</option>
                                    <option value="Mpesa">Mpesa</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <input type="checkbox" id="no_discount" name="no_discount" value="1" style="width: auto; margin-right: 5px;">
                                    <label for="no_discount" style="margin-bottom: 0;">No Discount</label>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filter</button>
                                <button type="button" class="btn btn-info" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                                <button type="button" class="btn btn-success" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function exportToExcel() {
                        const form = document.getElementById('filter-form');
                        const params = new URLSearchParams(new FormData(form)).toString();
                        window.location.href = 'export-excel.php?' + params;
                    }

                    function exportToPDF() {
                        const form = document.getElementById('filter-form');
                        const params = new URLSearchParams(new FormData(form)).toString();
                        window.location.href = 'export-pdf.php?' + params;
                    }
                </script>

                <!-- All Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> All Orders</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Qty</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $conn = getConnection();
                                    $result = $conn->query("
                                        SELECT o.id AS order_id, o.customer_name, o.customer_phone, o.payment_method, 
                                               o.status, o.discount_amount, oi.quantity, p.product_name, p.price, p.tax 
                                        FROM order_items oi 
                                        JOIN orders o ON oi.order_id = o.id 
                                        JOIN products p ON product_id = p.id 
                                        ORDER BY o.created_at DESC
                                    ");
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        $total = ($row['quantity'] * $row['price']) - $row['discount_amount'];
                                        $tax = $row['quantity'] * $row['tax'];
                                        $grand = $total + $tax;
                                        
                                        // Status badge
                                        $status_class = '';
                                        if ($row['status'] == 'pending') $status_class = 'status-pending';
                                        if ($row['status'] == 'delivered') $status_class = 'status-delivered';
                                        if ($row['status'] == 'cancelled') $status_class = 'status-cancelled';
                                        
                                        // Payment status
                                        $payment_class = $row['payment_method'] == 'Unpaid' ? 'payment-unpaid' : 'payment-paid';
                                        
                                        echo "<tr>
                                            <td>{$row['order_id']}</td>
                                            <td>{$row['product_name']}</td>
                                            <td>{$row['customer_name']}</td>
                                            <td>{$row['customer_phone']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td class='{$payment_class}'>{$row['payment_method']}</td>
                                            <td class='{$status_class}'>{$row['status']}</td>
                                            <td>KES ".number_format($grand, 2)."</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                        <input type='hidden' name='update_payment' value='1'>
                                                        <select name='payment_method' onchange='this.form.submit()' class='form-control' style='width:120px; display:inline-block;'>
                                                            <option value=''>Change Payment</option>
                                                            <option value='Mpesa'>Mpesa</option>
                                                            <option value='Cash'>Cash</option>
                                                            <option value='Unpaid'>Unpaid</option>
                                                        </select>
                                                    </form>
                                                    <a href='print_receipt.php?order_id={$row['order_id']}' class='btn btn-sm print-btn'><i class='fas fa-print'></i> Print</a>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Unpaid Orders</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount Due</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $unpaid = $conn->query("
                                        SELECT o.id AS order_id, o.customer_name, o.discount_amount, o.status,
                                               oi.quantity, p.price, p.tax 
                                        FROM order_items oi 
                                        JOIN orders o ON oi.order_id = o.id 
                                        JOIN products p ON oi.product_id = p.id 
                                        WHERE o.payment_method = 'Unpaid'
                                    ");
                                    
                                    while ($row = $unpaid->fetch_assoc()) {
                                        $total = ($row['quantity'] * $row['price']) - $row['discount_amount'];
                                        $tax = $row['quantity'] * $row['tax'];
                                        $grand = $total + $tax;
                                        
                                        // Status badge
                                        $status_class = '';
                                        if ($row['status'] == 'pending') $status_class = 'status-pending';
                                        if ($row['status'] == 'delivered') $status_class = 'status-delivered';
                                        if ($row['status'] == 'cancelled') $status_class = 'status-cancelled';
                                        
                                        echo "<tr>
                                            <td>{$row['order_id']}</td>
                                            <td>{$row['customer_name']}</td>
                                            <td>KES " . number_format($grand, 2) . "</td>
                                            <td class='{$status_class}'>{$row['status']}</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                        <input type='hidden' name='update_payment' value='1'>
                                                        <select name='payment_method' onchange='this.form.submit()' class='form-control' style='width:120px; display:inline-block;'>
                                                            <option value=''>Mark as Paid</option>
                                                            <option value='Mpesa'>Mpesa</option>
                                                            <option value='Cash'>Cash</option>
                                                        </select>
                                                    </form>
                                                    <a href='print_receipt.php?order_id={$row['order_id']}' class='btn btn-sm print-btn'><i class='fas fa-print'></i> Print</a>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Pending Orders</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Qty</th>
                                        <th>Payment</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $pending = $conn->query("
                                        SELECT o.id AS order_id, o.customer_name, o.customer_phone, o.payment_method, 
                                               o.status, o.discount_amount, oi.quantity, p.product_name, p.price, p.tax
                                        FROM orders o 
                                        JOIN order_items oi ON o.id = oi.order_id 
                                        JOIN products p ON oi.product_id = p.id 
                                        WHERE o.status = 'pending' 
                                        ORDER BY o.created_at DESC
                                    ");
                                    
                                    while ($row = $pending->fetch_assoc()) {
                                        $total = ($row['quantity'] * $row['price']) - $row['discount_amount'];
                                        $tax = $row['quantity'] * $row['tax'];
                                        $grand = $total + $tax;
                                        
                                        // Payment status
                                        $payment_class = $row['payment_method'] == 'Unpaid' ? 'payment-unpaid' : 'payment-paid';
                                        
                                        echo "<tr>
                                            <td>{$row['order_id']}</td>
                                            <td>
                                                <div style='display: flex; align-items: center; gap: 10px;'>
                                                   
                                                    <span>{$row['product_name']}</span>
                                                </div>
                                            </td>
                                            <td>{$row['customer_name']}</td>
                                            <td>{$row['customer_phone']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td class='{$payment_class}'>{$row['payment_method']}</td>
                                            <td>KES ".number_format($grand, 2)."</td>
                                            <td class='status-pending'>{$row['status']}</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                        <select name='status' onchange='this.form.submit()' class='form-control' style='width:120px; display:inline-block;'>
                                                            <option value=''>Change Status</option>
                                                            <option value='delivered'>Delivered</option>
                                                            <option value='cancelled'>Cancelled</option>
                                                        </select>
                                                        <input type='hidden' name='update_status' value='1'>
                                                    </form>
                                                    <a href='print_receipt.php?order_id={$row['order_id']}' class='btn btn-sm print-btn'><i class='fas fa-print'></i> Print</a>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-times-circle"></i> Cancelled Orders</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Qty</th>
                                        <th>Payment</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cancelled = $conn->query("
                                        SELECT o.id AS order_id, o.customer_name, o.customer_phone, o.payment_method, 
                                               o.status, o.discount_amount, oi.quantity, p.product_name, p.price, p.tax
                                        FROM orders o 
                                        JOIN order_items oi ON o.id = oi.order_id 
                                        JOIN products p ON oi.product_id = p.id 
                                        WHERE o.status = 'cancelled' 
                                        ORDER BY o.created_at DESC
                                    ");
                                    
                                    while ($row = $cancelled->fetch_assoc()) {
                                        $total = ($row['quantity'] * $row['price']) - $row['discount_amount'];
                                        $tax = $row['quantity'] * $row['tax'];
                                        $grand = $total + $tax;
                                        
                                        // Payment status
                                        $payment_class = $row['payment_method'] == 'Unpaid' ? 'payment-unpaid' : 'payment-paid';
                                        
                                        echo "<tr>
                                            <td>{$row['order_id']}</td>
                                            <td>
                                                <div style='display: flex; align-items: center; gap: 10px;'>
                                                    
                                                    <span>{$row['product_name']}</span>
                                                </div>
                                            </td>
                                            <td>{$row['customer_name']}</td>
                                            <td>{$row['customer_phone']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td class='{$payment_class}'>{$row['payment_method']}</td>
                                            <td>KES ".number_format($grand, 2)."</td>
                                            <td class='status-cancelled'>{$row['status']}</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                                        <select name='status' onchange='this.form.submit()' class='form-control' style='width:120px; display:inline-block;'>
                                                            <option value=''>Change Status</option>
                                                            <option value='pending'>Pending</option>
                                                            <option value='delivered'>Delivered</option>
                                                        </select>
                                                        <input type='hidden' name='update_status' value='1'>
                                                    </form>
                                                    <a href='print_receipt.php?order_id={$row['order_id']}' class='btn btn-sm print-btn'><i class='fas fa-print'></i> Print</a>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    
                                    $conn->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdownToggles = document.querySelectorAll(".dropdown-toggle");
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    const dropdown = this.closest(".dropdown");
                    dropdown.classList.toggle("open");
                    
                    // Close other dropdowns
                    document.querySelectorAll(".dropdown").forEach(other => {
                        if (other !== dropdown) {
                            other.classList.remove("open");
                        }
                    });
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener("click", function (e) {
                if (!e.target.closest(".dropdown")) {
                    document.querySelectorAll(".dropdown").forEach(dropdown => {
                        dropdown.classList.remove("open");
                    });
                }
            });
        });
    </script>
</body>
</html>