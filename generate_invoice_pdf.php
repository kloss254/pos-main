<?php
require('fpdf/fpdf.php');

// Disable error reporting output before PDF
ob_start();

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$invoice_id = (int)$_GET['id'];

// Fetch invoice and supplier details
$invoice_sql = "SELECT si.*, s.company_name AS supplier_name, s.email
                FROM supply_invoices si
                JOIN suppliers s ON si.supplier_id = s.id
                WHERE si.id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);
if ($invoice_result->num_rows === 0) {
    die("Invoice not found.");
}
$invoice = $invoice_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT sii.*, p.product_name
              FROM supply_invoice_items sii
              JOIN products p ON sii.product_id = p.id
              WHERE sii.invoice_id = $invoice_id";
$items_result = $conn->query($items_sql);

// Start PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Invoice Title
$pdf->SetTextColor(0, 100, 100);
$pdf->Cell(0,10,'INVOICE',0,1,'R');
$pdf->SetTextColor(0);

$pdf->SetFont('Arial','',12);
$pdf->Cell(100,8,"Invoice No: " . $invoice['invoice_number'], 0, 0);
$pdf->Cell(0,8,"Invoice Date: " . $invoice['invoice_date'], 0, 1);
$pdf->Cell(100,8,"S/C No: _________________________", 0, 0);
$pdf->Cell(0,8,"S/C Date: _________________________", 0, 1);
$pdf->Cell(100,8,"L/C No: _________________________", 0, 1);
$pdf->Ln(5);

// From and To
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,8,"To",0);
$pdf->Cell(0,8,"From",0,1);
$pdf->SetFont('Arial','',11);
$pdf->Cell(90,6,"Admin",0);
$pdf->Cell(0,6,$invoice['supplier_name'],0,1);
$pdf->Cell(90,6,"admin@email.com",0);
$pdf->Cell(0,6,$invoice['email'] ?? 'supplier@email.com',0,1);
$pdf->Cell(90,6,"Nairobi",0);
$pdf->Cell(0,6," Nairobi",0,1);
$pdf->Ln(8);

// Table Header
$pdf->SetFillColor(100,150,130);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(90,10,'Description',1,0,'C',true);
$pdf->Cell(20,10,'Qty',1,0,'C',true);
$pdf->Cell(30,10,'Price',1,0,'C',true);
$pdf->Cell(40,10,'Total',1,1,'C',true);

$pdf->SetTextColor(0);
$pdf->SetFont('Arial','',11);

$total = 0;
while ($item = $items_result->fetch_assoc()) {
    $line_total = $item['unit_price'] * $item['quantity'];
    $total += $line_total;

    $pdf->Cell(90,8,$item['product_name'],1);
    $pdf->Cell(20,8,$item['quantity'],1,0,'C');
    $pdf->Cell(30,8,number_format($item['unit_price'],2),1,0,'R');
    $pdf->Cell(40,8,number_format($line_total,2),1,1,'R');
}

// Summary Section
$pdf->Ln(4);
$pdf->Cell(140,8,'Sales Tax (10%)',0,0,'R');
$pdf->Cell(40,8,number_format($total * 0.1, 2),0,1,'R');
$pdf->Cell(140,8,'Shipping Charges',0,0,'R');
$pdf->Cell(40,8,'500.00',0,1,'R');
$pdf->SetFont('Arial','B',12);
$pdf->Cell(140,10,'Total Amount',0,0,'R');
$pdf->Cell(40,10,number_format($total * 1.1 + 500, 2),0,1,'R');

// Clean buffer and Output PDF
ob_end_clean();
$pdf->Output("I", "Invoice_{$invoice['invoice_number']}.pdf");
