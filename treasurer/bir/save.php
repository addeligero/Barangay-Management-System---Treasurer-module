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
    if (isset($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id'])) {
        $birId = intval($_POST['id']);
        $tin = $_POST['tin'];
        $payee = $_POST['payee'];
        $vat_type = $_POST['vat_type'] ?? 'Non-VAT';
        $gross_amount = $_POST['gross_amount'];
        $one_percent = $_POST['one_percent'];
        $five_percent = $_POST['five_percent'];
        $net_amount = $_POST['net_amount'];
        $record_date = $_POST['record_date'];
        $remarks = $_POST['remarks'] ?? '';
        $total_amount = $one_percent + $five_percent;

        $stmt = $conn->prepare("
            UPDATE bir_records
            SET tin = ?, payee = ?, vat_type = ?, gross_amount = ?, one_percent = ?, five_percent = ?, total_amount = ?, net_amount = ?, record_date = ?, remarks = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssdddddssi",
            $tin,
            $payee,
            $vat_type,
            $gross_amount,
            $one_percent,
            $five_percent,
            $total_amount,
            $net_amount,
            $record_date,
            $remarks,
            $birId
        );

        if ($stmt->execute()) {
            header("Location: list.php?updated=1");
        } else {
            header("Location: edit.php?id=" . $birId . "&error=1");
        }
        exit();
    }

    $tin = $_POST['tin'];
    $payee = $_POST['payee'];
    $vat_type = $_POST['vat_type'] ?? 'Non-VAT';
    $gross_amount = $_POST['gross_amount'];
    $one_percent = $_POST['one_percent'];
    $five_percent = $_POST['five_percent'];
    $net_amount = $_POST['net_amount'];
    $record_date = $_POST['record_date'];
    $remarks = $_POST['remarks'] ?? '';
    $total_amount = $one_percent + $five_percent;

    $stmt = $conn->prepare("
        INSERT INTO bir_records 
        (tin, payee, vat_type, gross_amount, one_percent, five_percent, total_amount, net_amount, record_date, remarks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssdddddss",
        $tin,
        $payee,
        $vat_type,
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
