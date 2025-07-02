<?php
// ✅ Always put these at the top level
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require('fpdf/fpdf.php');

$type = $_GET['type'] ?? 'pdf';

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch inventory log data with product name
$where = "WHERE 1=1";

if (!empty($_GET['product'])) {
    $productId = (int)$_GET['product'];
    $where .= " AND i.product_id = $productId";
}
if (!empty($_GET['user'])) {
    $user = $conn->real_escape_string($_GET['user']);
    $where .= " AND i.user LIKE '%$user%'";
}
if (!empty($_GET['from'])) {
    $from = $conn->real_escape_string($_GET['from']) . " 00:00:00";
    $where .= " AND i.timestamp >= '$from'";
}
if (!empty($_GET['to'])) {
    $to = $conn->real_escape_string($_GET['to']) . " 23:59:59";
    $where .= " AND i.timestamp <= '$to'";
}

$result = $conn->query("
    SELECT p.product_name, i.*
    FROM inventory_logs i
    JOIN products p ON i.product_id = p.id
    $where
    ORDER BY i.timestamp DESC
");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if ($type === 'excel') {
    // Generate Excel using PhpSpreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Inventory Logs");

    $sheet->fromArray(['Product', 'Action', 'Qty', 'Old Stock', 'New Stock', 'User', 'Timestamp'], NULL, 'A1');

    $rowNum = 2;
    foreach ($data as $log) {
        $sheet->fromArray([
            $log['product_name'], $log['action'], $log['quantity'],
            $log['old_stock'], $log['new_stock'],
            $log['user'], $log['timestamp']
        ], NULL, 'A' . $rowNum++);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inventory_logs.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");

} else {
    // Generate PDF using FPDF
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Inventory Logs Report', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 10);
    $headers = ['Product', 'Action', 'Qty', 'Old', 'New', 'User', 'Date/Time'];
    foreach ($headers as $header) {
        $pdf->Cell(40, 10, $header, 1);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($data as $log) {
        $pdf->Cell(40, 10, $log['product_name'], 1);
        $pdf->Cell(40, 10, $log['action'], 1);
        $pdf->Cell(40, 10, $log['quantity'], 1);
        $pdf->Cell(40, 10, $log['old_stock'], 1);
        $pdf->Cell(40, 10, $log['new_stock'], 1);
        $pdf->Cell(40, 10, $log['user'], 1);
        $pdf->Cell(40, 10, $log['timestamp'], 1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'inventory_logs.pdf');
}
