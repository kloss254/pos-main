<?php
// Handle new order form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $conn = new mysqli("localhost", "root", "", "pos");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $product_id = (int)$_POST['product_id'];
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
    $quantity = (int)$_POST['quantity'];
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $user_id = (int)$_POST['user_id'];
    $discount = (int)$_POST['discount'];

    $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, payment_method, user_id, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssi", $customer_name, $customer_phone, $payment_method, $user_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, discount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $order_id, $product_id, $quantity, $discount);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
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
        body, html {
            height: 100%;
            margin: 0;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
        }
        form {
            margin-bottom: 40px;
        }
        .filter-section {
            margin: 20px 0;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filter-row input,
        .filter-row select,
        .filter-row button {
            flex: 1 1 150px;
            max-width: 200px;
            padding: 6px;
        }
        .discount-check {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .weekly-report-btn {
            display: inline-block;
            padding: 10px 18px;
            background-color: #007bff;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .weekly-report-btn:hover {
            background-color: #2c3e50;
        }
        .btn-receipt {
            display: inline-block;
            padding: 6px 12px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-receipt:hover {
            background-color: #218838;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s ease;
            margin-right: 10px;
        }
        button:hover {
            background-color: #2c3e50;
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
            <li><a href="admin-dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="admin-orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
            <li><a href="admin-sales.php" class="active"><i class="fas fa-cash-register"></i> Sales</a></li>
            <li><a href="admin-products.php"><i class="fas fa-box"></i> Products</a></li>
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
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
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
            <section id="sales">
                <h2>Filter Sales</h2>
                <form method="GET" class="filter-section" id="filter-form">
                    <div class="filter-row">
                        <input type="text" name="customer_name" placeholder="Customer Name">
                        <input type="text" name="product_name" placeholder="Product Name">
                        <input type="text" name="user_id" placeholder="User ID">
                        <select name="payment_method">
                            <option value="">All Payments</option>
                            <option value="Mpesa">Mpesa</option>
                            <option value="Cash">Cash</option>
                            <option value="Unpaid">Unpaid</option>
                        </select>
                        <input type="date" name="created_date" placeholder="Created Date">
                        <label class="discount-check">
                            <input type="checkbox" name="no_discount" value="1"> No Discount
                        </label>
                        <button type="submit">Apply Filter</button>                            
                        <button type="button" onclick="window.location.href='sales-report.php'">Weekly Sales Report</button>
                    </div>
                </form><br>

                <h2>All Sales</h2>
                <div style="display: flex; margin: 10px 0; justify-content: flex-end; gap: 10px;">
                    <button onclick="exportDeliveredSalesPDF()" class="weekly-report-btn">Export Delivered PDF</button>
                    <button onclick="exportDeliveredSalesExcel()" class="weekly-report-btn">Export Delivered Excel</button>
                </div>

                <script>
                    function exportDeliveredSalesPDF() {
                        const form = document.getElementById('filter-form');
                        const params = new URLSearchParams(new FormData(form));
                        params.set('status', 'delivered');
                        window.location.href = 'export-delivered-pdf.php?' + params.toString();
                    }
                    function exportDeliveredSalesExcel() {
                        const form = document.getElementById('filter-form');
                        const params = new URLSearchParams(new FormData(form));
                        params.set('status', 'delivered');
                        window.location.href = 'export-delivered-excel.php?' + params.toString();
                    }
                </script><br>

                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Payment</th>
                            <th>User ID</th>
                            <th>Products</th>
                            <th>Discounts</th>
                            <th>Total Price</th>
                            <th>Total Tax</th>
                            <th>Created At</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filters = ["o.status = 'delivered'"];
if (!empty($_GET['customer_name'])) {
    $filters[] = "o.customer_name LIKE '%" . $conn->real_escape_string($_GET['customer_name']) . "%'";
}
if (!empty($_GET['payment_method'])) {
    $filters[] = "o.payment_method = '" . $conn->real_escape_string($_GET['payment_method']) . "'";
}
if (!empty($_GET['user_id'])) {
    $filters[] = "o.user_id = " . (int)$_GET['user_id'];
}
if (!empty($_GET['created_date'])) {
    $filters[] = "DATE(o.created_at) = '" . $conn->real_escape_string($_GET['created_date']) . "'";
}

$where = "WHERE " . implode(" AND ", $filters);

$result = $conn->query("
    SELECT 
        o.order_id,
        o.customer_name,
        o.customer_phone,
        o.payment_method,
        o.user_id,
        o.created_at,
        GROUP_CONCAT(
            CONCAT(
                p.product_name, ' x', oi.quantity,
                ' (Price:', p.price, ', Tax:', p.tax, '%)'
            ) SEPARATOR '<br>'
        ) AS products_html,
        GROUP_CONCAT(oi.discount SEPARATOR '<br>') AS discounts,
        SUM((oi.quantity - oi.discount) * p.price) AS total_price,
        SUM(oi.quantity * p.tax) AS total_tax
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    $where
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
");

$total_revenue = 0;
$total_tax_sum = 0;
$order_count = 0;

while ($row = $result->fetch_assoc()) {
    $total_revenue += $row['total_price'];
    $total_tax_sum += $row['total_tax'];
    $order_count++;

    echo "<tr>
        <td>{$row['order_id']}</td>
        <td>{$row['customer_name']}</td>
        <td>{$row['customer_phone']}</td>
        <td>{$row['payment_method']}</td>
        <td>{$row['user_id']}</td>
        <td>{$row['products_html']}</td>
        <td>{$row['discounts']}</td>
        <td>{$row['total_price']}</td>
        <td>{$row['total_tax']}</td>
        <td>{$row['created_at']}</td>
        <td>
          <a href='print_receipt.php?order_id={$row['order_id']}' target='_blank' class='btn-receipt'>Print Receipt</a>
        </td>
    </tr>";
}

if ($order_count > 0) {
    echo "<tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='7' style='text-align:right;'>Total Revenue:</td>
        <td>{$total_revenue}</td>
        <td>{$total_tax_sum}</td>
        <td colspan='2'></td>
    </tr>";
    echo "<tr style='font-weight:bold; background:#e0e0e0;'>
        <td colspan='11'>Total Delivered Orders: {$order_count}</td>
    </tr>";
} else {
    echo "<tr><td colspan='11'>No delivered sales found.</td></tr>";
}

$conn->close();
?>
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
                if (other !== dropdown) other.classList.remove("open");
            });
        });
    });
});
</script>
</body>
</html>
