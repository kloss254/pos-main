<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filters = ["o.status = 'delivered'"];

if (!empty($_GET['customer_name'])) {
    $filters[] = "o.customer_name LIKE '%" . $conn->real_escape_string($_GET['customer_name']) . "%'";
}
if (!empty($_GET['product_name'])) {
    $filters[] = "p.product_name LIKE '%" . $conn->real_escape_string($_GET['product_name']) . "%'";
}
if (!empty($_GET['user_id'])) {
    $filters[] = "o.user_id = " . (int)$_GET['user_id'];
}
if (!empty($_GET['payment_method'])) {
    $filters[] = "o.payment_method = '" . $conn->real_escape_string($_GET['payment_method']) . "'";
}
if (!empty($_GET['created_at'])) {
    $filters[] = "DATE(o.created_at) = '" . $conn->real_escape_string($_GET['created_at']) . "'";
}
if (isset($_GET['no_discount']) && $_GET['no_discount'] == '1') {
    $filters[] = "o.discounts = 0";
}

$where = "WHERE " . implode(" AND ", $filters);

$sql = "
    SELECT o.*, p.product_name, p.price, p.tax, p.image 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    $where
    ORDER BY o.created_at DESC
";

$result = $conn->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Delivered Sales');

$headers = ['Order ID', 'Product', 'Customer', 'Phone', 'Quantity', 'Payment Method', 'User ID', 'Discount', 'Price', 'Tax', 'Total Price', 'Total Tax', 'Created At'];
$sheet->fromArray($headers, NULL, 'A1');

$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $total_price = ($row['quantity'] - $row['discounts']) * $row['price'];
    $total_tax = $row['quantity'] * $row['tax'];

    $sheet->fromArray([
        $row['order_id'],
        $row['product_name'],
        $row['customer_name'],
        $row['customer_phone'],
        $row['quantity'],
        $row['payment_method'],
        $row['user_id'],
        $row['discounts'],
        $row['price'],
        $row['tax'],
        $total_price,
        $total_tax,
        $row['created_at']
    ], NULL, 'A' . $rowIndex);
    $rowIndex++;
}

$writer = new Xlsx($spreadsheet);
$filename = 'Delivered_Sales_Report.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer->save("php://output");
exit();
