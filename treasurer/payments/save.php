<?php
include "../../config/database.php";
include "../../config/session.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payer_name = $_POST['payer_name'];
    $service_type = $_POST['service_type'];
    $purpose = $_POST['purpose'];
    $amount = $_POST['amount'];
    $bir_tax = $_POST['bir_tax'];
    $receipt_no = $_POST['receipt_no'];
    $payment_date = $_POST['payment_date'];
    $remarks = $_POST['remarks'] ?? '';
    
    $stmt = $conn->prepare("
        INSERT INTO payments (payer_name, service_type, purpose, amount, bir_tax, receipt_no, payment_date, remarks, received_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("sssddsssi", $payer_name, $service_type, $purpose, $amount, $bir_tax, $receipt_no, $payment_date, $remarks, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        header("Location: list.php?success=1");
    } else {
        header("Location: add.php?error=Failed to save payment");
    }
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM payments WHERE id = $id");
    header("Location: list.php?deleted=1");
    exit;
}

header("Location: list.php");
exit;
