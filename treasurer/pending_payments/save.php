<?php
include "../../config/database.php";
include "../../config/session.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_paid') {
    $pendingId = intval($_POST['id'] ?? 0);

    if ($pendingId <= 0) {
        header("Location: list.php?error=Invalid pending payment ID.");
        exit;
    }

    $pendingStmt = $conn->prepare("SELECT * FROM payment_status WHERE id = ? AND payment_status = 'pending'");
    $pendingStmt->bind_param("i", $pendingId);
    $pendingStmt->execute();
    $pendingResult = $pendingStmt->get_result();

    if ($pendingResult->num_rows === 0) {
        $pendingStmt->close();
        header("Location: list.php?error=Pending payment not found or already paid.");
        exit;
    }

    $pending = $pendingResult->fetch_assoc();
    $pendingStmt->close();

    $lastReceipt = $conn->query("SELECT receipt_no FROM payments ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $nextReceipt = $lastReceipt ? (intval($lastReceipt['receipt_no']) + 1) : 100001;
    $paymentDate = date('Y-m-d');

    $received_by = null;
    if (!empty($_SESSION['user_id'])) {
        $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $userCheck->bind_param("i", $_SESSION['user_id']);
        $userCheck->execute();
        $userCheck->store_result();
        if ($userCheck->num_rows > 0) {
            $received_by = intval($_SESSION['user_id']);
        }
        $userCheck->close();
    }

    $remarks = "Pending Status #" . $pendingId;
    $operating_services = '';

    $conn->begin_transaction();

    $insertStmt = $conn->prepare("
        INSERT INTO payments (receipt_no, payment_date, payer_name, service_type, purpose, operating_services, amount, bir_tax, remarks, received_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $insertStmt->bind_param(
        "ssssssddsi",
        $nextReceipt,
        $paymentDate,
        $pending['resident_fname'],
        $pending['certificate_type'],
        $pending['purpose'],
        $operating_services,
        $pending['amount'],
        $pending['bir_tax'],
        $remarks,
        $received_by
    );
    $insertOk = $insertStmt->execute();
    $insertStmt->close();

    $updateOk = false;
    if ($insertOk) {
        $updateStmt = $conn->prepare("UPDATE payment_status SET payment_status = 'paid', created_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $pendingId);
        $updateOk = $updateStmt->execute();
        $updateStmt->close();
    }

    if ($insertOk && $updateOk) {
        $conn->commit();
        header("Location: list.php?paid=1");
    } else {
        $conn->rollback();
        header("Location: list.php?error=Failed to mark as paid.");
    }

    exit;
}

header("Location: list.php");
exit;
