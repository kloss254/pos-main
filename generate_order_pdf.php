<?php
require('fpdf/fpdf.php');

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("Invalid order ID");
}

// Fetch the order
$order_result = $conn->query("SELECT * FROM orders WHERE order_id = $order_id");
if ($order_result->num_rows == 0) {
    die("Order not found");
}
$order = $order_result->fetch_assoc();

// Fetch product info
$product_name = "Unknown";
$product_price = 0;
$product_result = $conn->query("SELECT product_name, price, tax FROM products WHERE id = " . (int)$order['product_id']);
if ($product_result->num_rows > 0) {
    $product = $product_result->fetch_assoc();
    $product_name = $product['product_name'];
    $product_price = $product['price'];
    $product_tax_rate = $product['tax'] ?? 0.05; // fallback to 5%
} else {
    $product_tax_rate = 0.05;
}

// Compute values
$quantity = (int)$order['quantity'];
$discount = (float)$order['discounts'];

$subtotal = $product_price * $quantity;
$total_tax = $subtotal * $product_tax_rate;
$total_after_discount = $subtotal - $discount;
$grand_total = $total_after_discount + $total_tax;

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 15, 'POS Order Receipt', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Generated on '.date("Y-m-d H:i").' - Page '.$this->PageNo(),0,0,'C');
    }

    function OrderDetails($order, $product_name, $product_price, $subtotal, $discount, $total_tax, $grand_total) {
        $this->SetFont('Arial', '', 12);
        $this->SetFillColor(240, 240, 240);

        $this->Cell(60, 10, 'Order ID:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['order_id'], 1, 1);

        $this->Cell(60, 10, 'Customer Name:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['customer_name'], 1, 1);

        $this->Cell(60, 10, 'Phone:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['customer_phone'], 1, 1);

        $this->Cell(60, 10, 'Product:', 1, 0, 'L', true);
        $this->Cell(130, 10, $product_name, 1, 1);

        $this->Cell(60, 10, 'Unit Price:', 1, 0, 'L', true);
        $this->Cell(130, 10, 'Ksh ' . number_format($product_price, 2), 1, 1);

        $this->Cell(60, 10, 'Quantity:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['quantity'], 1, 1);

        $this->Cell(60, 10, 'Subtotal:', 1, 0, 'L', true);
        $this->Cell(130, 10, 'Ksh ' . number_format($subtotal, 2), 1, 1);

        $this->Cell(60, 10, 'Discount:', 1, 0, 'L', true);
        $this->Cell(130, 10, 'Ksh ' . number_format($discount, 2), 1, 1);

        $this->Cell(60, 10, 'Tax (5%):', 1, 0, 'L', true);
        $this->Cell(130, 10, 'Ksh ' . number_format($total_tax, 2), 1, 1);

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(60, 10, 'Total Payable:', 1, 0, 'L', true);
        $this->Cell(130, 10, 'Ksh ' . number_format($grand_total, 2), 1, 1);
        $this->SetFont('Arial', '', 12);

        $this->Cell(60, 10, 'Payment Method:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['payment_method'], 1, 1);

        $this->Cell(60, 10, 'Status:', 1, 0, 'L', true);
        $this->Cell(130, 10, ucfirst($order['status']), 1, 1);

        $this->Cell(60, 10, 'Created At:', 1, 0, 'L', true);
        $this->Cell(130, 10, $order['created_at'], 1, 1);

        $this->Ln(10);
        $this->SetFont('Arial', 'I', 11);
        $this->Cell(0, 10, 'Thank you for shopping with us!', 0, 1, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->OrderDetails($order, $product_name, $product_price, $subtotal, $discount, $total_tax, $grand_total);
$pdf->Output("I", "Order_{$order['order_id']}_Receipt.pdf");
?>
