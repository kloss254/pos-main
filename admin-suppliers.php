<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filters and search
$statusFilter = $_GET['status'] ?? '';
$companyFilter = $_GET['company'] ?? '';
$searchTerm = $_GET['search'] ?? '';

$sql = "SELECT * FROM suppliers WHERE 1";
if ($statusFilter) $sql .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
if ($companyFilter) $sql .= " AND company_name LIKE '%" . $conn->real_escape_string($companyFilter) . "%'";
if ($searchTerm) $sql .= " AND (supplier_name LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%' OR phone LIKE '%$searchTerm%')";

$suppliers = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Supplier Management - POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        .btn {
            display: inline-flex;
            background-color: #2c3e50;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            align-items: center;
            font:bold;
            transition: background-color 0.3s ease;

        }

        .btn:hover {
            background-color: #1a252f;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-left: auto;
            padding: 12px 15px;
            display: flex
;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
            flex-wrap: wrap;
        }

       .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }


        .filter-form input,
        .filter-form select,
        .filter-form button,
        .filter-form a.btn {
            padding: 4px 10px;
            font-size: 14px;
            height: 45px;
            line-height: 34px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .filter-form button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .filter-form a.btn {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            display: flex;
        }

        .filter-form a.btn:hover {
            background-color: #0056b3;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 10px;
        }

        th, td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #eaeaea;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-inactive {
            color: red;
            font-weight: bold;
        }

        .action-links a {
            text-decoration: none;
            margin-right: 8px;
        }

        .action-links a:hover {
            text-decoration: underline;
        }

        #wrapper {
            display: flex;
        }



        #main-content {
            flex: 1;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        <header class="main-header">
            <h2>Supplier List</h2>
        </header>
        <main>
            <form class="filter-form" method="GET">
                <input type="text" name="search" placeholder="Search suppliers" value="<?= htmlspecialchars($searchTerm) ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Active" <?= $statusFilter == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $statusFilter == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <input type="text" name="company" placeholder="Company" value="<?= htmlspecialchars($companyFilter) ?>">
               <div class="button-group">
                    <button type="submit">Apply Filter</button>
                    <a href="export-suppliers.php?type=pdf&<?= http_build_query($_GET) ?>" class="btn">Export PDF</a>
                    <a href="export-suppliers.php?type=excel&<?= http_build_query($_GET) ?>" class="btn">Export Excel</a>
                </div>


            </form>


            <a href="add-supplier.php" class="btn">➕ Add Supplier</a>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $suppliers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($row['contact_person']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td class="<?= $row['status'] == 'Active' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </td>
                        <td class="action-links">
                            <a href="edit-supplier.php?id=<?= $row['id'] ?>">✏️ Edit</a>
                            <a href="delete-supplier.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this supplier?')">🗑️ Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
