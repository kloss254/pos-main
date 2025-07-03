<?php
$conn = new mysqli("localhost", "root", "", "pos");
$id = (int)$_GET['id'];
$conn->query("DELETE FROM suppliers WHERE id = $id");
header("Location: admin-suppliers.php");
