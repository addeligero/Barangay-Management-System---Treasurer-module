<?php
session_start();
include "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        // password_verify is REQUIRED (secure)
        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // role-based redirect
            if ($user['role'] === 'treasurer') {
                header("Location: treasurer/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;

        } else {
            $error = "Invalid password";
        }

    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Barangay System Login</title>
</head>

<body>

    <h2>Barangay System</h2>

    <form method="POST">
        Username: <input type="text" name="username" required><br><br>
        Password: <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p style="color:red;"><?= $error ?></p>

</body>

</html>