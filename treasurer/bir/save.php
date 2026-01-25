<?php
include "../../config/database.php";
include "../../config/session.php";

// Handle DELETE request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM bir_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: list.php?deleted=1");
    } else {
        header("Location: list.php?error=1");
    }
    exit();
}

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
