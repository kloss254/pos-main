<?php
$conn = new mysqli("localhost", "root", "", "pos");
if (isset($_GET['barcode'])) {
    $barcode = $conn->real_escape_string($_GET['barcode']);
    $result = $conn->query("SELECT * FROM products WHERE barcode = '$barcode'");

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(null);
    }
}
?>
