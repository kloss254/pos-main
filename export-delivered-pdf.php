<?php
require('fpdf/fpdf.php');
$conn = new mysqli("localhost", "root", "", "pos");

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

$sql = "SELECT o.*, p.product_name, p.price, p.tax, p.image 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        $where ORDER BY o.created_at DESC";
$result = $conn->query($sql);

// Initialize PDF in landscape A4
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(44, 62, 80); // #2c3e50
$pdf->Cell(0, 12, 'Delivered Orders Report', 0, 1, 'C');

// Headers and widths
$headers = ['Order ID', 'Product ID', 'Customer', 'Phone', 'Qty', 'Payment', 'User ID', 'Discount', 'Product', 'Price', 'Tax', 'Total', 'Tax Amt', 'Created'];
$widths  = [18, 22, 30, 25, 10, 15, 15, 15, 30, 18, 15, 18, 18, 50];

// Table header
$pdf->SetFillColor(44, 62, 80);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
foreach ($headers as $i => $header) {
    $pdf->Cell($widths[$i], 9, $header, 1, 0, 'C', true);
}
$pdf->Ln();

// Table rows
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(44, 62, 80);
$pdf->SetFillColor(236, 240, 241);
$rowToggle = false;

while ($row = $result->fetch_assoc()) {
    $rowToggle = !$rowToggle;
    $fill = $rowToggle ? true : false;

    $total_price = ($row['quantity'] - $row['discounts']) * $row['price'];
    $total_tax = $row['quantity'] * $row['tax'];

    $values = [
        $row['order_id'],
        $row['product_id'],
        $row['customer_name'],
        $row['customer_phone'],
        $row['quantity'],
        $row['payment_method'],
        $row['user_id'],
        $row['discounts'],
        $row['product_name'],
        '' . number_format($row['price'], 2),
        $row['tax'] . '%',
        '' . number_format($total_price, 2),
        '' . number_format($total_tax, 2),
        $row['created_at']
    ];

    foreach ($values as $i => $val) {
        $pdf->Cell($widths[$i], 8, substr($val, 0, 40), 1, 0, 'L', $fill);
    }
    $pdf->Ln();
}

$pdf->Output('D', 'delivered_orders_report.pdf');
exit;
