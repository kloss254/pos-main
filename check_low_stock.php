<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$threshold = 49;
$result = $conn->query("SELECT * FROM products WHERE stock <= $threshold");

$alertCount = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Low Stock Alert - POS</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f0f0f0;
        }
        .low-stock {
            color: red;
            font-weight: bold;
        }
        .alert {
            background: #ffe6e6;
            color: #b30000;
            padding: 10px;
            margin: 15px 0;
            border-left: 5px solid #cc0000;
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
                <li><a href="admin-dashboard.php" ><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="admin-orders.php" ><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="admin-sales.php" ><i class="fas fa-cash-register"></i> Sales</a></li>
                <li><a href="admin-products.php" ><i class="fas fa-box"></i> Products</a></li>
                <li class="dropdown open">
                    <li class="dropdown open">
                        <a href="#" class="dropdown-toggle active">
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
                <h2>Low Stock Alerts</h2>
            </header>

            <main>
                <?php if ($alertCount > 0): ?>
                    <div class='alert'>⚠️ You have <?= $alertCount ?> product(s) with low stock.</div>

                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td>KES <?= number_format($row['price'], 2) ?></td>
                                <td><?= $row['stock'] ?></td>
                                <td class="low-stock">Low Stock</td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="margin-top: 20px;">✅ All products are sufficiently stocked.</p>
                <?php endif; ?>
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
