<?php
// Handle form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $discounts = (int)$_POST['discounts'];

    $stmt = $conn->prepare("INSERT INTO orders (product_id, customer_name, customer_phone, quantity, payment_method, user_id, discounts) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issisis", $product_id, $customer_name, $customer_phone, $quantity, $payment_method, $user_id, $discounts);
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
        form input, form select {
            display: block;
            margin-bottom: 10px;
            padding: 6px;
            width: 100%;
            max-width: 300px;
        }
        form button {
            padding: 8px 16px;
        }
        .order-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            max-width: 800px;
        }
        .order-form-column {
            display: flex;
            flex-direction: column;
        }
        .order-form-column label,
        .order-form-column input,
        .order-form-column select,
        .order-form-column button {
            margin-bottom: 10px;
        }
        .filter-section {
            margin: 20px 0;
        }
        .filter-section .filter-row {
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

        button {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.2s ease, transform 0.1s ease;
        margin-right: 10px;
        }

         button:hover {
            background-color: #2c3e50;
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
                <li><a href="admin-orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="admin-sales.php"  class="active"><i class="fas fa-cash-register"></i> Sales</a></li>
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
                    

                        <!-- Export Buttons -->
                       <div style="display: flex; margin: 10px 0; justify-content: flex-end; gap: 10px;">
                            <button onclick="exportDeliveredSalesPDF()" class="weekly-report-btn"> Export Delivered PDF</button>
                            <button onclick="exportDeliveredSalesExcel()" class="weekly-report-btn"> Export Delivered Excel</button>
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
                                    <th>Product ID</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Quantity</th>
                                    <th>Payment</th>
                                    <th>User ID</th>
                                    <th>Discount</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Tax</th>
                                    <th>Total Price</th>
                                    <th>Total Tax</th>
                                    <th>Image</th>
                                    <th>Created At</th>
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
                                    $filters[] = "customer_name LIKE '%" . $conn->real_escape_string($_GET['customer_name']) . "%'";
                                }
                                if (!empty($_GET['product_name'])) {
                                    $filters[] = "p.product_name LIKE '%" . $conn->real_escape_string($_GET['product_name']) . "%'";
                                }
                                if (!empty($_GET['user_id'])) {
                                    $filters[] = "user_id = " . (int)$_GET['user_id'];
                                }
                                if (!empty($_GET['payment_method'])) {
                                    $filters[] = "payment_method = '" . $conn->real_escape_string($_GET['payment_method']) . "'";
                                }
                                if (!empty($_GET['created_date'])) {
                                    $filters[] = "DATE(o.created_at) = '" . $conn->real_escape_string($_GET['created_date']) . "'";
                                }
                                if (isset($_GET['no_discount']) && $_GET['no_discount'] == '1') {
                                    $filters[] = "discounts = 0";
                                }

                                $where = "WHERE " . implode(" AND ", $filters);

                                $result = $conn->query("
                                    SELECT o.*, p.product_name, p.price, p.tax, p.image 
                                    FROM orders o
                                    JOIN products p ON o.product_id = p.id
                                    $where
                                    ORDER BY o.created_at DESC
                                ");

                                $total_revenue = 0;
                                $total_tax_sum = 0;
                                $order_count = 0;

                                while ($row = $result->fetch_assoc()) {
                                    $total_price = ($row['quantity'] - $row['discounts']) * $row['price'];
                                    $total_tax = $row['quantity'] * $row['tax'];
                                    $total_revenue += $total_price;
                                    $total_tax_sum += $total_tax;
                                    $order_count++;

                                    echo "<tr>
                                        <td>{$row['order_id']}</td>
                                        <td>{$row['product_name']}</td>
                                        <td>{$row['customer_name']}</td>
                                        <td>{$row['customer_phone']}</td>
                                        <td>{$row['quantity']}</td>
                                        <td>{$row['payment_method']}</td>
                                        <td>{$row['user_id']}</td>
                                        <td>{$row['discounts']}</td>
                                        <td>{$row['product_name']}</td>
                                        <td>{$row['price']}</td>
                                        <td>{$row['tax']}%</td>
                                        <td>{$total_price}</td>
                                        <td>{$total_tax}</td>
                                        <td><img src='uploads/{$row['image']}' style='width:40px;'></td>
                                        <td>{$row['created_at']}</td>
                                    </tr>";
                                }

                                echo "<tr style='font-weight:bold; background:#f0f0f0;'>
                                    <td colspan='11' style='text-align:right;'>Total Revenue:</td>
                                    <td>{$total_revenue}</td>
                                    <td>{$total_tax_sum}</td>
                                    <td colspan='2'></td>
                                </tr>";
                                echo "<tr style='font-weight:bold; background:#e0e0e0;'>
                                    <td colspan='15'>Total Delivered Orders: {$order_count}</td>
                                </tr>";

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
