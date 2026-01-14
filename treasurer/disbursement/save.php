<?php
include "../../config/database.php";
include "../../config/session.php";

$stmt = $conn->prepare("
INSERT INTO disbursements
(disburse_date, check_no, payee, dv_no, amount, fund, purpose, release_amount)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssdsdd",
    $_POST['date'],
    $_POST['check_no'],
    $_POST['payee'],
    $_POST['dv_no'],
    $_POST['amount'],
    $_POST['fund'],
    $_POST['purpose'],
    $_POST['release']
);

$stmt->execute();

header("Location: list.php");
exit;
