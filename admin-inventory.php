<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alertCount = $conn->query("SELECT COUNT(*) AS low FROM products WHERE stock <= 5")->fetch_assoc()['low'];
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS System Dashboard</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <h2>Inventory Management</h2>
                <div class="header-actions">
                    <button class="action-btn" onclick="window.location.href='add-product.html'"><i class="fas fa-plus"></i> Add Product</button>
                </div>
            </header>

            <main>
                <?php if ($alertCount > 0): ?>
                    <div class="alert">⚠️ You have <?= $alertCount ?> product(s) with low stock.</div>
                <?php endif; ?>

                <section id="manual-stock-update">
                    <h3>Update Stock Manually</h3>
                    <form id="stock-update-form">
                        <input type="text" name="product_name" placeholder="Product Name" required />
                        <input type="number" name="new_stock" placeholder="New Stock Value" required />
                        <button type="submit">Update Stock</button>
                    </form>
                    <script>
                        document.getElementById('stock-update-form').addEventListener('submit', function(e) {
                            e.preventDefault();
                            const formData = new FormData(this);
                            fetch('update_stock.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.text())
                            .then(alert);
                        });
                    </script>
                </section>

                <section id="inventory">
                    <h2>Inventory Overview</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td>KES <?= number_format($row['price'], 2) ?></td>
                                    <td><?= $row['stock'] ?></td>
                                    <td class="<?= ($row['stock'] <= 5) ? 'low-stock' : '' ?>">
                                        <?= ($row['stock'] <= 5) ? 'Low Stock' : 'In Stock' ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
