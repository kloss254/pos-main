<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$type = $_GET['type'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// --------------- Helper Date Filter ---------------
$whereClause = '';
$params = [];
$types = '';

if ($startDate && $endDate) {
    $whereClause = "AND DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types = 'ss';
}

// -------------------------------------------------
// --------------- Delivered Orders ---------------
// -------------------------------------------------
if ($type === 'pdf' || $type === 'excel') {

    $query = "
    SELECT 
      o.order_id, 
      o.customer_name, 
      o.created_at, 
      o.payment_method,
      oi.quantity,
      oi.discount,
      p.product_name, 
      p.price, 
      p.tax
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE o.status = 'delivered' $whereClause
    ORDER BY o.created_at DESC
";

    $stmt = $conn->prepare($query);
    if ($whereClause) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    $totalSales = 0;
    $totalDiscount = 0;
    $totalTax = 0;

    while ($row = $result->fetch_assoc()) {
        $quantity = $row['quantity'];
        $discount = $row['discount'] ?? 0;
        $price = $row['price'];
        $tax = $row['tax'];

        $subtotal = ($quantity - $discount) * $price;
        $taxAmount = $quantity * $tax;

        $row['subtotal'] = $subtotal;
        $row['tax_amount'] = $taxAmount;

        $totalSales += $subtotal;
        $totalDiscount += $discount * $price;
        $totalTax += $taxAmount;

        $data[] = $row;
    }

    $totalProfit = $totalSales - $totalDiscount - $totalTax;

    if ($type === 'pdf') {
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->SetTextColor(0, 102, 204);
        $pdf->Cell(0,10,'POS Delivered Orders Report',0,1,'C');
        $pdf->SetTextColor(0,0,0);

        $pdf->SetFont('Arial','',12);
        if ($startDate && $endDate) {
            $pdf->Cell(0,10,"Period: $startDate to $endDate",0,1,'C');
        }
        $pdf->Ln(5);

        $pdf->SetFillColor(200,220,255);
        $pdf->SetFont('Arial','B',10);
        $headers = ['ID','Product','Qty','Discount','Price','Tax','Subtotal','Date'];
        foreach ($headers as $col) {
            $pdf->Cell(25,8,$col,1,0,'C',true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        foreach ($data as $row) {
            $pdf->Cell(25,8,$row['order_id'],1);
            $pdf->Cell(25,8,substr($row['product_name'],0,12),1);
            $pdf->Cell(25,8,$row['quantity'],1);
            $pdf->Cell(25,8,$row['discount'],1);
            $pdf->Cell(25,8,number_format($row['price'],2),1);
            $pdf->Cell(25,8,number_format($row['tax_amount'],2),1);
            $pdf->Cell(25,8,number_format($row['subtotal'],2),1);
            $pdf->Cell(25,8,substr($row['created_at'],0,10),1);
            $pdf->Ln();
        }

        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,8,"Summary",0,1);
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(60,8,"Total Sales:",0,0);
        $pdf->Cell(30,8,"KES " . number_format($totalSales,2),0,1);
        $pdf->Cell(60,8,"Total Discounts:",0,0);
        $pdf->Cell(30,8,"KES " . number_format($totalDiscount,2),0,1);
        $pdf->Cell(60,8,"Total Tax:",0,0);
        $pdf->Cell(30,8,"KES " . number_format($totalTax,2),0,1);
        $pdf->Cell(60,8,"Estimated Profit:",0,0);
        $pdf->Cell(30,8,"KES " . number_format($totalProfit,2),0,1);

        $pdf->Output('I', 'DeliveredOrdersReport.pdf');
        exit;
    }

    if ($type === 'excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Delivered Orders');

        $sheet->fromArray([
            ['ID','Product','Quantity','Discount','Price','Tax','Subtotal','Date']
        ], NULL, 'A1');

        $rowNum = 2;
        foreach ($data as $row) {
            $sheet->fromArray([
                $row['order_id'],
                $row['product_name'],
                $row['quantity'],
                $row['discount'],
                $row['price'],
                $row['tax_amount'],
                $row['subtotal'],
                $row['created_at']
            ], NULL, "A$rowNum");
            $rowNum++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="DeliveredOrdersReport.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

// -------------------------------------------------
// --------------- Supply Invoices -----------------
// -------------------------------------------------
if ($type === 'supplier_pdf' || $type === 'supplier_excel') {

    $invoiceQuery = "
        SELECT si.*, s.supplier_name
        FROM supply_invoices si
        LEFT JOIN suppliers s ON si.supplier_id = s.id
        ORDER BY si.invoice_date DESC
    ";
    $invoiceResult = $conn->query($invoiceQuery);
    $invoices = [];
    while ($row = $invoiceResult->fetch_assoc()) {
        $invoices[] = $row;
    }

    if ($type === 'supplier_pdf') {
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->SetTextColor(0,102,204);
        $pdf->Cell(0,10,'Supplier Invoices Report',0,1,'C');
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(5);

        $pdf->SetFillColor(200,220,255);
        $pdf->SetFont('Arial','B',10);
        $headers = ['ID','Invoice No','Supplier','Date','Amount','Created By'];
        foreach ($headers as $col) {
            $pdf->Cell(32,8,$col,1,0,'C',true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        foreach ($invoices as $inv) {
            $pdf->Cell(32,8,$inv['id'],1);
            $pdf->Cell(32,8,$inv['invoice_number'],1);
            $pdf->Cell(32,8,substr($inv['supplier_name'] ?? 'Unknown',0,12),1);
            $pdf->Cell(32,8,$inv['invoice_date'],1);
            $pdf->Cell(32,8,number_format($inv['total_amount'],2),1);
            $pdf->Cell(32,8,$inv['created_by'],1);
            $pdf->Ln();
        }

        $pdf->Output('I', 'SupplierInvoicesReport.pdf');
        exit;
    }

    if ($type === 'supplier_excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Supplier Invoices');

        $sheet->fromArray([
            ['ID','Invoice No','Supplier','Date','Amount','Created By']
        ], NULL, 'A1');

        $rowNum = 2;
        foreach ($invoices as $inv) {
            $sheet->fromArray([
                $inv['id'],
                $inv['invoice_number'],
                $inv['supplier_name'],
                $inv['invoice_date'],
                $inv['total_amount'],
                $inv['created_by']
            ], NULL, "A$rowNum");
            $rowNum++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="SupplierInvoicesReport.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

// -------------------------------------------------
// --------------- Inventory Logs ------------------
// -------------------------------------------------
if ($type === 'logs_pdf' || $type === 'logs_excel') {

    $logQuery = "
        SELECT il.*, p.product_name
        FROM inventory_logs il
        LEFT JOIN products p ON il.product_id = p.id
        ORDER BY il.timestamp DESC
        LIMIT 50
    ";
    $result = $conn->query($logQuery);
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    if ($type === 'logs_pdf') {
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',16);
        $pdf->SetTextColor(0,102,204);
        $pdf->Cell(0,10,'Inventory Logs Report',0,1,'C');
        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(5);

        $pdf->SetFillColor(200,220,255);
        $pdf->SetFont('Arial','B',10);
        $headers = ['ID','Product','Action','Qty','Old','New','User','Date'];
        foreach ($headers as $col) {
            $pdf->Cell(25,8,$col,1,0,'C',true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
        foreach ($logs as $log) {
            $pdf->Cell(25,8,$log['id'],1);
            $pdf->Cell(25,8,substr($log['product_name'],0,12),1);
            $pdf->Cell(25,8,$log['action'],1);
            $pdf->Cell(25,8,$log['quantity'],1);
            $pdf->Cell(25,8,$log['old_stock'],1);
            $pdf->Cell(25,8,$log['new_stock'],1);
            $pdf->Cell(25,8,$log['user'],1);
            $pdf->Cell(25,8,substr($log['timestamp'],0,10),1);
            $pdf->Ln();
        }

        $pdf->Output('I', 'InventoryLogsReport.pdf');
        exit;
    }

    if ($type === 'logs_excel') {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory Logs');

        $sheet->fromArray([
            ['ID','Product','Action','Qty','Old','New','User','Date']
        ], NULL, 'A1');

        $rowNum = 2;
        foreach ($logs as $log) {
            $sheet->fromArray([
                $log['id'],
                $log['product_name'],
                $log['action'],
                $log['quantity'],
                $log['old_stock'],
                $log['new_stock'],
                $log['user'],
                $log['timestamp']
            ], NULL, "A$rowNum");
            $rowNum++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="InventoryLogsReport.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

// --------------- Invalid Type ---------------
echo "Invalid export type.";
?>
