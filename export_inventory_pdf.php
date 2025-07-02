<?php
require('fpdf/fpdf.php');
$conn = new mysqli("localhost", "root", "", "pos");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Product'); $pdf->Cell(40,10,'Price'); $pdf->Cell(30,10,'Stock'); $pdf->Ln();

$result = $conn->query("SELECT * FROM products");
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60,10, $row['product_name']); 
    $pdf->Cell(40,10, $row['price']); 
    $pdf->Cell(30,10, $row['stock']); 
    $pdf->Ln();
}
$pdf->Output('D', 'inventory_report.pdf');
?>