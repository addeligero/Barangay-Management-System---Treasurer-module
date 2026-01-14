<?php
include "../../config/database.php";

$one = $_POST['base'] * 0.01;
$five = $_POST['base'] * 0.05;

$stmt = $conn->prepare("
INSERT INTO bir_records
(tin,payee,gross_amount,base_amount,one_percent,five_percent)
VALUES (?,?,?,?,?,?)
");

$stmt->bind_param(
    "ssdddd",
    $_POST['tin'],
    $_POST['payee'],
    $_POST['gross'],
    $_POST['base'],
    $one,
    $five
);

$stmt->execute();
header("Location: list.php");
