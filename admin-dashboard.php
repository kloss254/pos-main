<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products with stock
$products_sql = "SELECT product_name, stock FROM products";
$products_result = $conn->query($products_sql);

// Fetch order history (oldest first)
$order_history_sql = "
    SELECT p.product_name, o.quantity, o.created_at
    FROM orders o
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at ASC
";
$order_history_result = $conn->query($order_history_sql);

// Fetch latest 5 sales (newest first)
$new_sales_sql = "
    SELECT p.product_name, o.quantity, o.created_at
    FROM orders o
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$new_sales_result = $conn->query($new_sales_sql);

// Convert results to arrays to allow multiple iterations
$products_data = [];
if ($products_result && $products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products_data[] = $row;
    }
}

$order_history_data = [];
if ($order_history_result && $order_history_result->num_rows > 0) {
    while ($row = $order_history_result->fetch_assoc()) {
        $order_history_data[] = $row;
    }
}

$new_sales_data = [];
if ($new_sales_result && $new_sales_result->num_rows > 0) {
    while ($row = $new_sales_result->fetch_assoc()) {
        $new_sales_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>POS System Dashboard</title>
    <link rel="stylesheet" href="styles.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
    <style>

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .low-stock {
            color: red;
            font-weight: bold;
        }
        .dropdown {
            position: relative;
        }
        .dropdown ul {
            left: 20px;
            top: 100%;
            background: #f8f8f8;
            list-style: none;
            padding: 5px;
        }
        .dropdown:hover ul {
            display: block;
        }
        .dropdown ul li a {
            display: block;
            padding: 5px 10px;
            color: #333;
            text-decoration: none;
        }
        .dropdown ul li a:hover {
            background: #e0e0e0;
        }
        .sidebar-menu .dropdown-menu {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.6s ease, opacity 0.6s ease;
            flex-direction: column;
            background-color: #34495e;
            font-size: 0.9em;
            padding-left: 10px;
        }

        .sidebar-menu .dropdown.open .dropdown-menu {
            max-height: 600px;
            opacity: 1;
        }

        .sidebar-menu .dropdown-toggle {
            display: block;
            width: 100%;
            padding: 12px 20px;
            color: #fff;
            text-align: left;
            border: none;
            background-color: #2c3e50;
            cursor: pointer;
            font-size: 1em;
        }

        .sidebar-menu .dropdown-toggle:hover {
            background-color: #34495e;
        }

        .caret-icon {
            transition: transform 0.6s ease;
        }

        .dropdown.open .caret-icon {
            transform: rotate(180deg);
        }

        .sidebar-menu .dropdown-menu li a {
            padding: 8px 30px;
            color: #ccc;
            display: block;
        }

        .sidebar-menu .dropdown-menu li a:hover {
            background-color: #3e556e;
            color: #fff;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <aside id="sidebar">
        <div class="sidebar-header">
            <h1>POS Dashboard</h1>
        </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin-orders.php" ><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="admin-sales.php" ><i class="fas fa-cash-register"></i> Sales</a></li>
                <li><a href="admin-products.php" ><i class="fas fa-box"></i> Products</a></li>
                <li class="dropdown open">
                    <li class="dropdown ">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-warehouse"></i> Inventory
                            <i class="fas fa-caret-down caret-icon" style="float: right;"></i>
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
                    <li class="dropdown ">
                    <a href="#" class="dropdown-toggle "> 
                        <i class="fas fa-truck"></i> Suppliers
                        <i class="fas fa-caret-down caret-icon" style="float: right;"></i>
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
            <h2>Admin Stats</h2>
            <div class="header-actions">
                <button class="action-btn"><i class="fas fa-edit"></i> Edit</button>
                <button class="action-btn primary-btn"><i class="fas fa-plus"></i> Add Widget</button>
            </div>
        </header>

        <main>
            <section id="dashboard">
                <h2>Dashboard</h2>
                <div class="dashboard-cards">
                    <!-- Products Available -->
                    <div class="card">
                        <h3>📦 Products Available</h3>
                        <ul>
                            <?php foreach ($products_data as $row): ?>
                                <li><?= htmlspecialchars($row['product_name']) ?> — <strong><?= htmlspecialchars($row['stock']) ?> in stock</strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Order History (oldest first) -->
                    <div class="card">
                        <h3>📈 Order History</h3>
                        <ul>
                            <?php foreach ($order_history_data as $row): ?>
                                <li><?= htmlspecialchars($row['product_name']) ?> — Qty: <?= htmlspecialchars($row['quantity']) ?> — <strong><em><?= htmlspecialchars($row['created_at']) ?></em></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- New Sales (latest 5) -->
                    <div class="card">
                        <h3>🛒 New Sales</h3>
                        <ul>
                            <?php foreach ($new_sales_data as $row): ?>
                                <li><?= htmlspecialchars($row['product_name']) ?> — Qty: <?= htmlspecialchars($row['quantity']) ?> — <strong><?= htmlspecialchars($row['created_at']) ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
   <script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggles = document.querySelectorAll(".dropdown-toggle");

        toggles.forEach(toggle => {
            toggle.addEventListener("click", function (e) {
                e.preventDefault();
                const dropdown = this.closest(".dropdown");
                dropdown.classList.toggle("open");

                document.querySelectorAll(".dropdown").forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove("open");
                    }
                });
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
