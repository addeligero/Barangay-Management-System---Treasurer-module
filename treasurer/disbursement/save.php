<?php
include "../../config/database.php";
include "../../config/session.php";

// Handle DELETE request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM disbursements WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: list.php?deleted=1");
    } else {
        header("Location: list.php?error=1");
    }
    exit();
}

// Handle INSERT request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
