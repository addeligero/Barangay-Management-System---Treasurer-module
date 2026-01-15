<?php
include "../../config/database.php";
session_start();

$stmt = $conn->prepare("
INSERT INTO cedula
(cedula_no, or_number, full_name, address, birth_date, age, sex, birth_place, civil_status, occupation, tin, height, weight, amount, nature_of_collection, issued_date, remarks, issued_by)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "sssssissssdddssssi",
    $_POST['cedula_no'],
    $_POST['or_number'],
    $_POST['full_name'],
    $_POST['address'],
    $_POST['birth_date'],
    $_POST['age'],
    $_POST['sex'],
    $_POST['birth_place'],
    $_POST['civil_status'],
    $_POST['occupation'],
    $_POST['tin'],
    $_POST['height'],
    $_POST['weight'],
    $_POST['amount'],
    $_POST['nature_of_collection'],
    $_POST['issued_date'],
    $_POST['remarks'],
    $_SESSION['user_id']
);

$stmt->execute();
header("Location: list.php");
