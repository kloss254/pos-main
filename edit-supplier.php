<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is set and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h2>❌ Invalid supplier ID.</h2>";
    exit;
}

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM suppliers WHERE id = $id");
$data = $result->fetch_assoc();

if (!$data) {
    echo "<h2>❌ Supplier not found.</h2>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name=?, contact_person=?, email=?, phone=?, company_name=?, address=?, notes=?, status=? WHERE id=?");
    $stmt->bind_param(
        "ssssssssi",
        $_POST['supplier_name'],
        $_POST['contact_person'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['company_name'],
        $_POST['address'],
        $_POST['notes'],
        $_POST['status'],
        $id
    );
    $stmt->execute();
    header("Location: admin-suppliers.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Supplier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
        }
        form input, form textarea, form select, form button {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        form h2 {
            margin-bottom: 15px;
        }
        button {
            background-color: #2c3e50;
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background-color: #1a252f;
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
                <li><a href="admin-sales.php"><i class="fas fa-cash-register"></i> Sales</a></li>
                <li><a href="admin-products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin-inventory.php"><i class="fas fa-warehouse"></i> Inventory</a></li>
                <li><a href="admin-suppliers.php" class="active"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li><a href="admin-categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li><a href="admin-orders.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="admin-reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div id="main-content">
            <header class="main-header">
                <h2>Edit Supplier</h2>
            </header>
            <main>
                <form method="POST">
                    <input type="text" name="supplier_name" value="<?= htmlspecialchars($data['supplier_name']) ?>" required placeholder="Supplier Name">
                    <input type="text" name="contact_person" value="<?= htmlspecialchars($data['contact_person']) ?>" placeholder="Contact Person">
                    <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" placeholder="Email">
                    <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" placeholder="Phone">
                    <input type="text" name="company_name" value="<?= htmlspecialchars($data['company_name']) ?>" placeholder="Company Name">
                    <textarea name="address" placeholder="Address"><?= htmlspecialchars($data['address']) ?></textarea>
                    <textarea name="notes" placeholder="Notes"><?= htmlspecialchars($data['notes']) ?></textarea>
                    <select name="status">
                        <option value="Active" <?= $data['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= $data['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <button type="submit"><i class="fas fa-save"></i> Update Supplier</button>
                </form>
            </main>
        </div>
    </div>
</body>
</html>
