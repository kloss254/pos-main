<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$type = $_GET['type'] ?? '';

$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filters
$status = $_GET['status'] ?? '';
$company = $_GET['company'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM suppliers WHERE 1";
if ($status) $sql .= " AND status = '" . $conn->real_escape_string($status) . "'";
if ($company) $sql .= " AND company_name LIKE '%" . $conn->real_escape_string($company) . "%'";
if ($search) $sql .= " AND (supplier_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";

$result = $conn->query($sql);

switch ($type) {
    case 'pdf':
        require('fpdf/fpdf.php');

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Supplier Report', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(30, 10, 'Name', 1);
        $pdf->Cell(30, 10, 'Contact', 1);
        $pdf->Cell(40, 10, 'Email', 1);
        $pdf->Cell(30, 10, 'Phone', 1);
        $pdf->Cell(30, 10, 'Company', 1);
        $pdf->Cell(20, 10, 'Status', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(30, 10, $row['supplier_name'], 1);
            $pdf->Cell(30, 10, $row['contact_person'], 1);
            $pdf->Cell(40, 10, $row['email'], 1);
            $pdf->Cell(30, 10, $row['phone'], 1);
            $pdf->Cell(30, 10, $row['company_name'], 1);
            $pdf->Cell(20, 10, $row['status'], 1);
            $pdf->Ln();
        }

        // Send PDF as a download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="suppliers.pdf"');
        header('Cache-Control: max-age=0');
        $pdf->Output('D', 'suppliers.pdf');
        exit;

    case 'excel':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Suppliers");

        $sheet->fromArray(['Name', 'Contact', 'Email', 'Phone', 'Company', 'Status'], NULL, 'A1');

        $rowIndex = 2;
        while ($row = $result->fetch_assoc()) {
            $sheet->fromArray([
                $row['supplier_name'],
                $row['contact_person'],
                $row['email'],
                $row['phone'],
                $row['company_name'],
                $row['status']
            ], NULL, 'A' . $rowIndex++);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="suppliers.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    default:
        echo "❌ Invalid export type specified.";
}
?>
