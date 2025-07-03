<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$alertCount = $conn->query("SELECT COUNT(*) AS low FROM products WHERE stock <= 5")->fetch_assoc()['low'];

// Handle stock addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity_added = (int)$_POST['quantity'];
    $user = $_SESSION['username'] ?? 'Admin';

    $productQuery = $conn->query("SELECT stock FROM products WHERE id = $product_id");
    if ($productQuery->num_rows > 0) {
        $product = $productQuery->fetch_assoc();
        $old_stock = (int)$product['stock'];
        $new_stock = $old_stock + $quantity_added;

        $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");

        $stmt = $conn->prepare("INSERT INTO inventory_logs (product_id, action, quantity, old_stock, new_stock, user) VALUES (?, 'Stock Added', ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $product_id, $quantity_added, $old_stock, $new_stock, $user);
        $stmt->execute();

        // ✅ Redirect to avoid resubmission
        header("Location: update_stock.php?success=1");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Update Stock</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'admin-styles.css'; ?>
        #main-content {
            margin-left: 250px; /* Adjust depending on sidebar width */
            padding: 20px 30px;
            background-color: #f4f6f9;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            box-sizing: border-box;
        }

        .main-header {
            background-color: #ffffff;
            padding: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .main-header h2 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .log-card {
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

form input[type="number"] {
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-right: 10px;
}

form input[type="submit"].btn {
    background-color: #007bff;
    color: white;
    padding: 8px 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

form input[type="submit"].btn:hover {
    background-color: #0056b3;
}
/* Main Content Layout */
#main-content {
    padding: 30px;
    background-color: #f4f6f9;
    min-height: 100vh;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
}


/* Header Section */
.main-header {
    background-color: #ffffff;
    padding: 20px;
    border-bottom: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.main-header h2 {
    margin: 0;
    font-size: 26px;
    font-weight: 600;
    color: #34495e;
}

/* Product Card for Each Product */
.log-card {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

/* Inventory Table */
.log-card table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.log-card table th,
.log-card table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.log-card table th {
    background-color: #f8f9fa;
    color: #333;
    font-weight: 600;
}

/* Add Stock Form */
.log-card form {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-card input[type="number"] {
    width: 100px;
    padding: 7px 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.log-card input[type="submit"].btn {
    padding: 8px 16px;
    background-color: #007bff;
    border: none;
    color: #fff;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.log-card input[type="submit"].btn:hover {
    background-color: #0056b3;
}

/* Alerts */
.alert {
    padding: 12px 18px;
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    border-radius: 6px;
    margin-bottom: 25px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
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
            <h2>Update Stock</h2>
        </header>
        <main>
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="alert" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                    ✅ Stock successfully updated!
                </div>
            <?php endif; ?>
            <?php if ($alertCount > 0): ?>
                <div class="alert">⚠️ You have <?php echo $alertCount; ?> product(s) with low stock.</div>
            <?php endif; ?>

            <?php
            $products = $conn->query("SELECT * FROM products");
            while ($product = $products->fetch_assoc()):
                $product_id = $product['id'];
                $logs = $conn->query("SELECT * FROM inventory_logs WHERE product_id = $product_id ORDER BY timestamp DESC");
            ?>
                <div class="log-card">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?> (Stock: <?php echo $product['stock']; ?> | Price: KES <?php echo $product['price']; ?>)</h3>

                    <form method="POST" style="margin-bottom:15px;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <label>Add Quantity:</label>
                        <input type="number" name="quantity" min="1" required>
                        <input type="submit" name="add_stock" value="Add Stock" class="btn">
                    </form>

                    <?php if ($logs->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Qty</th>
                                    <th>Old</th>
                                    <th>New</th>
                                    <th>User</th>
                                    <th>Date/Time</th>
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
                        <p>No inventory logs for this product.</p>
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
