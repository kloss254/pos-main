<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$conn = new mysqli("localhost", "root", "", "pos");
$result = $conn->query("SELECT * FROM products");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Product', 'Price', 'Stock'], NULL, 'A1');

$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue("A$row", $data['product_name']);
    $sheet->setCellValue("B$row", $data['price']);
    $sheet->setCellValue("C$row", $data['stock']);
    $row++;
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="inventory.xlsx"');
$writer->save("php://output");
?>
