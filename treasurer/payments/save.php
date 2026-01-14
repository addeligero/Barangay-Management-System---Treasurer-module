<?php
include "../../config/database.php";
include "../../config/session.php";

$stmt = $conn->prepare("
  INSERT INTO payments
  (payer_name, service_type, purpose, amount, bir_tax, receipt_no, received_by)
  VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssddsi",
    $_POST['payer_name'],
    $_POST['service_type'],
    $_POST['purpose'],
    $_POST['amount'],
    $_POST['bir_tax'],
    $_POST['receipt_no'],
    $_SESSION['user_id']
);

$stmt->execute();

header("Location: list.php");
exit;
