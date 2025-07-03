<?php
include 'session.php'; // For user access control if needed

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set default date range to last 7 days if not manually filtered
$productFilter = $_GET['product'] ?? '';
$userFilter = $_GET['user'] ?? '';

// Automatically set default dates if not provided
$fromDate = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
$toDate = $_GET['to'] ?? date('Y-m-d');

$exportQuery = http_build_query([
    'product' => $productFilter,
    'user' => $userFilter,
    'from' => $fromDate,
    'to' => $toDate
]);


// Alert for low stock
$alertCount = $conn->query("SELECT COUNT(*) AS low FROM products WHERE stock <= 5")->fetch_assoc()['low'];
$productFilter = $_GET['product'] ?? '';
$userFilter = $_GET['user'] ?? '';
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';
$exportQuery = http_build_query([
    'product' => $productFilter,
    'user' => $userFilter,
    'from' => $fromDate,
    'to' => $toDate
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Inventory Logs</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .alert {
            background-color: #fff3cd;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .log-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 30px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filter-form input,
        .filter-form select {
            padding: 6px;
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
    .dropdown ul {
        left: 20px;
        top: 100%;
        background: #f8f8f8;
        list-style: none;
        padding: 5px;
    }
    .filter-form button,
    .filter-form .btn {
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: flex-end;
    }

    .filter-form button:hover,
    .filter-form .btn:hover {
        background-color: #2c3e50;
    }

    .filter-form .btn {
        text-decoration: none;
        display: inline-block;
        margin-left: 5px;
    }

    .filter-form {
        align-items: center;
    }
    /* Align filter form items in a row and push buttons to the right */
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    /* Container for right-aligned buttons */
    .button-group {
        display: flex;
        gap: 10px;
        margin-left: auto;
    }

    /* Filter button – green */
    .filter-form button {
        padding: 8px 16px;
        background-color: #28a745; /* Green */
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: background-color 0.3s ease, transform 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .filter-form button:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    /* Export buttons */
    .filter-form .btn {
        padding: 8px 16px;
        background-color: #007bff; /* Blue */
        color: white;
        text-decoration: none;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .filter-form .btn:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
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
            <h2>Inventory Logs</h2>
        </header>
        <main>
            <?php if ($alertCount > 0): ?>
                <div class="alert">⚠️ You have <?php echo $alertCount; ?> product(s) with low stock.</div>
            <?php endif; ?>

            <!-- Filter Form -->
            <form class="filter-form" method="GET">
                <div class="filter-fields">
                    <select name="product">
                        <option value="">-- All Products --</option>
                        <?php
                        $productList = $conn->query("SELECT id, product_name FROM products");
                        while ($p = $productList->fetch_assoc()) {
                            $sel = ($p['id'] == $productFilter) ? 'selected' : '';
                            echo "<option value='{$p['id']}' $sel>{$p['product_name']}</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="user" placeholder="User" value="<?php echo htmlspecialchars($userFilter); ?>">
                    <input type="date" name="from" value="<?php echo $fromDate; ?>">
                    <input type="date" name="to" value="<?php echo $toDate; ?>">
                </div><hr>

                <div class="button-group">
                    <button type="submit">Apply Filter</button>
                    <a href="export-inventory.php?type=pdf&<?php echo $exportQuery; ?>" class="btn">Export PDF</a>
                    <a href="export-inventory.php?type=excel&<?php echo $exportQuery; ?>" class="btn">Export Excel</a>
                </div>
            </form>


            <!-- Logs Display -->
            <?php
            $products = $conn->query("SELECT * FROM products");
            while ($product = $products->fetch_assoc()):
                if (!empty($productFilter) && $productFilter != $product['id']) continue;

                $product_id = $product['id'];
                $logSql = "SELECT * FROM inventory_logs WHERE product_id = $product_id";
                if (!empty($userFilter)) {
                    $logSql .= " AND user LIKE '%" . $conn->real_escape_string($userFilter) . "%'";
                }
                if (!empty($fromDate)) {
                    $fromTimestamp = $conn->real_escape_string($fromDate) . " 00:00:00";
                    $logSql .= " AND timestamp >= '$fromTimestamp'";
                }
                if (!empty($toDate)) {
                    $toTimestamp = $conn->real_escape_string($toDate) . " 23:59:59";
                    $logSql .= " AND timestamp <= '$toTimestamp'";
                }
                $logSql .= " ORDER BY timestamp DESC";

                $logs = $conn->query($logSql);
            ?>
                <div class="log-card">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?> (Stock: <?php echo $product['stock']; ?>)</h3>
                    <?php if ($logs->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th> Action</th>
                                    <th> Qty</th>
                                    <th> Old</th>
                                    <th> New</th>
                                    <th> User</th>
                                    <th> Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $log['action']; ?></td>
                                        <td><?php echo $log['quantity']; ?></td>
                                        <td><?php echo $log['old_stock']; ?></td>
                                        <td><?php echo $log['new_stock']; ?></td>
                                        <td><?php echo $log['user']; ?></td>
                                        <td><?php echo $log['timestamp']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No logs found for this product.</p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
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
