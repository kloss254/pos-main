<?php
include 'session.php';

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$invoice_id = (int)$_GET['id'];

// Fetch invoice and supplier
$invoice_sql = "SELECT si.*, si.invoice_date, s.company_name AS supplier_name
                FROM supply_invoices si
                JOIN suppliers s ON si.supplier_id = s.id
                WHERE si.id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);
if ($invoice_result->num_rows === 0) {
    echo "Invoice not found.";
    exit;
}
$invoice = $invoice_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT sii.*, p.product_name
              FROM supply_invoice_items sii
              JOIN products p ON sii.product_id = p.id
              WHERE sii.invoice_id = $invoice_id";
$items_result = $conn->query($items_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Supply Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }
        #wrapper {
            display: flex;
        }
        #main-content {
            flex: 1;
            padding: 20px;
            background-color: #f9f9f9;
            min-height: 100vh;
        }
   
        .invoice-header {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
            
        }
        th {
            background: #eaeaea;
            color: #333;
        }
        .btn-back, .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .btn-back {
            background-color: #28a745;
            color: #fff;
        }
        .btn-back:hover {
            background-color: #218838;
        }

        .btn-print {
            background-color: #007bff;
            color: #fff;
        }
        .btn-print:hover {
            background-color: #0056b3;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }



        /* Print styles */
        @media print {
            aside#sidebar,
            .btn-back,
            .btn-print {
                display: none !important;
            }
            body {
                margin: 0;
                background: white;
            }
            #main-content {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            table {
                box-shadow: none;
                border: 1px solid #000;
            }
            th {
                background-color: #ddd !important;
                color: black;
            }
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
        <div class="invoice-header">
            <h2>Supply Invoice: <?= htmlspecialchars($invoice['invoice_number']) ?></h2>
            <p><strong>Supplier:</strong> <?= htmlspecialchars($invoice['supplier_name']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($invoice['invoice_date']) ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity Supplied</th>
                    <th>Cost per Unit (KES)</th>
                    <th>Total Cost (KES)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                $total_qty = 0;
                while ($item = $items_result->fetch_assoc()):
                    $total = $item['quantity'] * $item['unit_price'];
                    $grand_total += $total;
                    $total_qty += $item['quantity'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= number_format($total, 2) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <th>Total</th>
                    <th><?= $total_qty ?></th>
                    <th></th>
                    <th><?= number_format($grand_total, 2) ?> KES</th>
                </tr>
            </tbody>

        </table>

        <div class="button-group">
            <a href="supply_invoices_list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to List</a>
            <a href="generate_invoice_pdf.php?id=<?= $invoice_id ?>" class="btn-print" target="_blank">
                <i class="fas fa-print"></i> Print / Export PDF
            </a>

        </div>
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
