<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_invoice'])) {
    $supplier_id = (int)$_POST['supplier_id'];
    $invoice_number = $conn->real_escape_string($_POST['invoice_number']);
    $invoice_date = $conn->real_escape_string($_POST['invoice_date']);
    $created_by = $_SESSION['username'] ?? 'Admin';

    $products = $_POST['products'] ?? [];
    $total_amount = 0;

    foreach ($products as $item) {
        if (isset($item['unit_price'], $item['quantity'])) {
            $total_amount += $item['unit_price'] * $item['quantity'];
        }
    }

    // Insert invoice (with created_by)
    $stmt = $conn->prepare("INSERT INTO supply_invoices (invoice_number, supplier_id, invoice_date, total_amount, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisis", $invoice_number, $supplier_id, $invoice_date, $total_amount, $created_by);
    $stmt->execute();
    if ($stmt->error) die("Invoice Insert Error: " . $stmt->error);

    $invoice_id = $stmt->insert_id;

    // Insert each item and update stock
    foreach ($products as $item) {
        if (!isset($item['product_id'], $item['quantity'], $item['unit_price'])) continue;

        $product_id = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['unit_price'];
        $total_price = $quantity * $unit_price;

        $stmt = $conn->prepare("INSERT INTO supply_invoice_items (invoice_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $invoice_id, $product_id, $quantity, $unit_price, $total_price);
        $stmt->execute();
        if ($stmt->error) die("Item Insert Error: " . $stmt->error);

        $old_stock = $conn->query("SELECT stock FROM products WHERE id = $product_id")->fetch_assoc()['stock'];
        $new_stock = $old_stock + $quantity;
        $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");

        $conn->query("INSERT INTO inventory_logs (product_id, action, quantity, old_stock, new_stock, user) 
                      VALUES ($product_id, 'Supplied', $quantity, $old_stock, $new_stock, '$created_by')");
    }

    header("Location: supply_invoices_list.php?success=1");
    exit();
}

// Fetch suppliers and products
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers");
$products = $conn->query("SELECT id, product_name FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Supply Invoice</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        #wrapper { display: flex; }
        #main-content { flex: 1; padding: 30px; background: #f9f9f9; }
        .form-section { margin-bottom: 30px; }
        h2 { margin-top: 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        table th, table td { border: 1px solid #ccc; padding: 10px; }
        input[type="number"], select, input[type="text"], input[type="date"] {
            padding: 6px; width: 100%; box-sizing: border-box;
        }
        .btn {
            padding: 15px 15px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            width: 200px;
        }
        .btn:hover { background: #218838; }
        .form-grid { display: flex; gap: 20px; flex-wrap: wrap; }
        .form-field { flex: 1; min-width: 200px; }
        .form-field label { display: block; margin-bottom: 6px; font-weight: bold; }
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
    <script>
        let rowIndex = 0;
        const productOptions = `<?php
            $products->data_seek(0);
            while($p = $products->fetch_assoc()) {
                echo '<option value="' . $p['id'] . '">' . htmlspecialchars($p['product_name']) . '</option>';
            }
        ?>`;

        function addProductRow() {
            const table = document.getElementById('product-rows');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><select name="products[${rowIndex}][product_id]" required>${productOptions}</select></td>
                <td><input type="number" name="products[${rowIndex}][quantity]" min="1" required></td>
                <td><input type="number" name="products[${rowIndex}][unit_price]" step="0.01" required></td>
                <td><button type="button" onclick="this.closest('tr').remove()">Remove</button></td>
            `;
            table.appendChild(row);
            rowIndex++;
        }

        window.onload = function () {
            addProductRow();
        };
    </script>
</head>
<body>
<div id="wrapper">
    <aside id="sidebar">
        <div class="sidebar-header">
            <h1>POS Dashboard</h1>
        </div>
            <ul class="sidebar-menu">
                <li><a href="admin-dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
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
                    <li class="dropdown open">
                    <a href="#" class="dropdown-toggle active"> 
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
        <h2>Add Supply Invoice</h2>
        <form method="POST">
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" required>
                    </div>
                    <div class="form-field">
                        <label>Supplier</label>
                        <select name="supplier_id" required>
                            <option value="">-- Select Supplier --</option>
                            <?php $suppliers->data_seek(0); while($s = $suppliers->fetch_assoc()): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Date</label>
                        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Products</h3>
                <table>
                    <thead>
                        <tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Action</th></tr>
                    </thead>
                    <tbody id="product-rows"></tbody>
                </table>
                <button type="button" class="btn" onclick="addProductRow()">Add Product</button>
            </div>

            <input type="submit" name="submit_invoice" value="Submit Invoice" class="btn">
        </form>
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
