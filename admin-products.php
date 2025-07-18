<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$targetDir = "uploads/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// ADD product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_product"])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $tax = $_POST['tax'];
    $barcode = $_POST['barcode'];

    $imagePath = "placeholder.jpg";
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $imageName = uniqid() . "_" . basename($image['name']);
        $imagePath = $targetDir . $imageName;
        move_uploaded_file($image['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, image, tax, barcode) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisis", $product_name, $price, $stock, $imagePath, $tax, $barcode);
    $stmt->execute();
    $stmt->close();
}

// DELETE product
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $deleteId");
}

// EDIT product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_product"])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $tax = $_POST['tax'];
    $barcode = $_POST['barcode'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $imageName = uniqid() . "_" . basename($image['name']);
        $imagePath = $targetDir . $imageName;
        move_uploaded_file($image['tmp_name'], $imagePath);
        $conn->query("UPDATE products SET product_name='$product_name', price=$price, stock=$stock, tax=$tax, barcode='$barcode', image='$imagePath' WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET product_name='$product_name', price=$price, stock=$stock, tax=$tax, barcode='$barcode' WHERE id=$id");
    }
}

$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>POS System Dashboard</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .action-buttons button { margin: 2px; }
        .product-image { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        input[type="text"], input[type="number"] { width: 150px; padding: 5px; }
        .dropdown { position: relative; }
        .dropdown ul { left: 20px; top: 100%; background: #f8f8f8; list-style: none; padding: 5px; }
        .dropdown:hover ul { display: block; }
        .dropdown ul li a { display: block; padding: 5px 10px; color: #333; text-decoration: none; }
        .dropdown ul li a:hover { background: #e0e0e0; }
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
        
        /* Custom file input styling */
        .file-input-container {
            position: relative;
            display: inline-block;
            margin: 10px 0;
        }
        
        .file-input-label {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .file-input-label:hover {
            background: #2980b9;
        }
        
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-name {
            margin-left: 10px;
            font-size: 0.9em;
            color: #555;
        }
        
        .image-preview-container {
            margin: 10px 0;
            text-align: center;
        }
        
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: none;
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
            <li><a href="admin-sales.php"><i class="fas fa-cash-register"></i> Sales</a></li>
            <li><a href="admin-products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li class="dropdown">
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
            <h2>Product Management</h2>
        </header>

        <main>
            <section id="products">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data" id="addProductForm">
                    <input type="text" name="product_name" placeholder="Product Name" required />
                    <input type="number" name="price" placeholder="Price" step="0.01" required />
                    <input type="number" name="stock" placeholder="Stock" required />
                    <input type="number" name="tax" placeholder="Tax %" step="0.01" required />
                    <input type="text" name="barcode" placeholder="Barcode (optional)" />
                    
                    <div class="file-input-container">
                        <label for="add-product-image" class="file-input-label">
                            <i class="fas fa-image"></i> Choose Image
                        </label>
                        <input type="file" name="image" id="add-product-image" class="file-input" accept="image/*">
                        <span id="add-file-name" class="file-name">No file chosen</span>
                    </div>
                    
                    <div class="image-preview-container">
                        <img id="add-image-preview" class="image-preview" alt="Image preview">
                    </div>
                    
                    <button type="submit" name="add_product">Add Product</button>
                </form>

                <h3>Product List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Tax</th>
                            <th>Stock</th>
                            <th>Barcode</th>
                            <th>Image</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <form method="POST" enctype="multipart/form-data" class="product-form">
                                    <td><?= $i++ ?></td>
                                    <td><input type="text" name="product_name" value="<?= htmlspecialchars($row['product_name']) ?>"></td>
                                    <td><input type="number" name="price" value="<?= $row['price'] ?>" step="0.01"></td>
                                    <td><input type="number" name="tax" value="<?= $row['tax'] ?>" step="0.01"></td>
                                    <td><input type="number" name="stock" value="<?= $row['stock'] ?>"></td>
                                    <td><input type="text" name="barcode" value="<?= htmlspecialchars($row['barcode']) ?>"></td>
                                    <td>
                                        <img src="<?= $row['image'] ?>" class="product-image" alt="Product Image">
                                        
                                        <div class="file-input-container" style="margin-top: 5px;">
                                            <label for="image-<?= $row['id'] ?>" class="file-input-label" style="padding: 5px 10px; font-size: 0.8em;">
                                                <i class="fas fa-sync-alt"></i> Change
                                            </label>
                                            <input type="file" name="image" id="image-<?= $row['id'] ?>" class="file-input" accept="image/*">
                                        </div>
                                        
                                        <div class="image-preview-container">
                                            <img id="preview-<?= $row['id'] ?>" class="image-preview" alt="Image preview">
                                        </div>
                                    </td>
                                    <td><?= $row['created_at'] ?></td>
                                    <td class="action-buttons">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="update_product" class="update-btn">Update</button>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">
                                            <button type="button" class="delete-btn">Delete</button>
                                        </a>
                                    </td>
                                </form>
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
        // Dropdown menu functionality
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
        
        // File input handling for add product form
        const addProductImageInput = document.getElementById('add-product-image');
        const addFileNameSpan = document.getElementById('add-file-name');
        const addImagePreview = document.getElementById('add-image-preview');
        
        if (addProductImageInput) {
            addProductImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    addFileNameSpan.textContent = this.files[0].name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        addImagePreview.src = e.target.result;
                        addImagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(this.files[0]);
                } else {
                    addFileNameSpan.textContent = 'No file chosen';
                    addImagePreview.style.display = 'none';
                }
            });
        }
        
        // File input handling for edit product forms
        document.querySelectorAll('.product-form').forEach(form => {
            const fileInput = form.querySelector('.file-input');
            const previewId = 'preview-' + form.querySelector('input[name="id"]').value;
            const imagePreview = document.getElementById(previewId);
            
            if (fileInput && imagePreview) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            imagePreview.style.display = 'block';
                        }
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        imagePreview.style.display = 'none';
                    }
                });
            }
        });
    });
</script>
</body>
</html>

<?php $conn->close(); ?>