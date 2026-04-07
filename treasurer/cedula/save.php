<?php
include "../../config/database.php";
include "../../config/session.php";

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM cedula WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: list.php?deleted=1");
    exit();
}

// Handle insert or update action (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id'])) {
        $cedulaId = intval($_POST['id']);
        $annual_income = isset($_POST['annual_income']) ? floatval($_POST['annual_income']) : 0;
        $height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : 0;
        $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : 0;
        $amount = floatval($_POST['amount']);

        $stmt = $conn->prepare("
            UPDATE cedula SET
                cedula_no = ?,
                or_number = ?,
                full_name = ?,
                address = ?,
                birth_date = ?,
                age = ?,
                sex = ?,
                birth_place = ?,
                civil_status = ?,
                citizenship = ?,
                occupation = ?,
                tin = ?,
                height = ?,
                weight = ?,
                annual_income = ?,
                amount = ?,
                nature_of_collection = ?,
                issued_date = ?,
                remarks = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssssissssssddddsssi",
            $_POST['cedula_no'],
            $_POST['or_number'],
            $_POST['full_name'],
            $_POST['address'],
            $_POST['birth_date'],
            $_POST['age'],
            $_POST['sex'],
            $_POST['birth_place'],
            $_POST['civil_status'],
            $_POST['citizenship'],
            $_POST['occupation'],
            $_POST['tin'],
            $height,
            $weight,
            $annual_income,
            $amount,
            $_POST['nature_of_collection'],
            $_POST['issued_date'],
            $_POST['remarks'],
            $cedulaId
        );

        if ($stmt->execute()) {
            header("Location: list.php?updated=1");
        } else {
            header("Location: edit.php?id=" . $cedulaId . "&error=Failed to update cedula");
        }
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO cedula
        (cedula_no, or_number, full_name, address, birth_date, age, sex, birth_place, civil_status, citizenship, occupation, tin, height, weight, annual_income, amount, nature_of_collection, issued_date, remarks, issued_by)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $annual_income = isset($_POST['annual_income']) ? floatval($_POST['annual_income']) : 0;
    $height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : 0;
    $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : 0;
    $amount = floatval($_POST['amount']);

    // Safely resolve issued_by — use NULL if session user doesn't exist in users table
    $issued_by = null;
    if (!empty($_SESSION['user_id'])) {
        $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $userCheck->bind_param("i", $_SESSION['user_id']);
        $userCheck->execute();
        $userCheck->store_result();
        if ($userCheck->num_rows > 0) {
            $issued_by = intval($_SESSION['user_id']);
        }
        $userCheck->close();
    }

    $stmt->bind_param(
        "sssssissssssddddsssi",
        $_POST['cedula_no'],
        $_POST['or_number'],
        $_POST['full_name'],
        $_POST['address'],
        $_POST['birth_date'],
        $_POST['age'],
        $_POST['sex'],
        $_POST['birth_place'],
        $_POST['civil_status'],
        $_POST['citizenship'],
        $_POST['occupation'],
        $_POST['tin'],
        $height,
        $weight,
        $annual_income,
        $amount,
        $_POST['nature_of_collection'],
        $_POST['issued_date'],
        $_POST['remarks'],
        $issued_by
    );

    $stmt->execute();
    header("Location: list.php?success=1");
    exit();
}
