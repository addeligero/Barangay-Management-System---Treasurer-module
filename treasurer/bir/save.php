<?php
include "../../config/database.php";
include "../../config/session.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tin = $_POST['tin'];
    $payee = $_POST['payee'];
    $gross_amount = $_POST['gross_amount'];
    $one_percent = $_POST['one_percent'];
    $five_percent = $_POST['five_percent'];
    $net_amount = $_POST['net_amount'];
    $record_date = $_POST['record_date'];
    $remarks = $_POST['remarks'] ?? '';
    $total_amount = $one_percent + $five_percent;

    $stmt = $conn->prepare("
        INSERT INTO bir_records 
        (tin, payee, gross_amount, one_percent, five_percent, total_amount, net_amount, record_date, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdddddss",
        $tin,
        $payee,
        $gross_amount,
        $one_percent,
        $five_percent,
        $total_amount,
        $net_amount,
        $record_date,
        $remarks
    );

    if ($stmt->execute()) {
        header("Location: list.php?success=1");
    } else {
        header("Location: add.php?error=1");
    }
    exit();
}
