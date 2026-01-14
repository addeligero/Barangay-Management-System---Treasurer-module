<?php
include "../../config/database.php";
session_start();

$stmt = $conn->prepare("
INSERT INTO cedula
(full_name,address,age,birth_place,occupation,tin,amount,issued_date,issued_by)
VALUES (?,?,?,?,?,?,?,CURDATE(),?)
");

$stmt->bind_param(
    "ssisssdi",
    $_POST['full_name'],
    $_POST['address'],
    $_POST['age'],
    $_POST['birth_place'],
    $_POST['occupation'],
    $_POST['tin'],
    $_POST['amount'],
    $_SESSION['user_id']
);

$stmt->execute();
header("Location: list.php");
