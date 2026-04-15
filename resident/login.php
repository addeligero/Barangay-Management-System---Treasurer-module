<?php
session_start();
include "../config/database.php";

if (isset($_SESSION['resident_id'])) {
    header("Location: pending_payments.php");
    exit;
}

$error = "";
$notice = "";

if (isset($_GET['error']) && $_GET['error'] === 'session') {
    $notice = "Please login to continue.";
} elseif (isset($_GET['error']) && $_GET['error'] === 'account') {
    $error = "Account not found or inactive. Please contact the barangay office.";
}

function build_resident_name(array $resident, string $middleMode = 'full'): string
{
    $first = trim($resident['first_name'] ?? '');
    $middle = trim($resident['middle_name'] ?? '');
    $surname = trim($resident['surname'] ?? '');
    $suffix = trim($resident['suffix'] ?? '');

    $parts = [];
    if ($first !== '') {
        $parts[] = $first;
    }

    if ($middle !== '') {
        if ($middleMode === 'initial') {
            $parts[] = strtoupper(substr($middle, 0, 1)) . '.';
        } elseif ($middleMode === 'full') {
            $parts[] = $middle;
        }
    }

    if ($surname !== '') {
        $parts[] = $surname;
    }

    if ($suffix !== '') {
        $parts[] = $suffix;
    }

    return trim(implode(' ', $parts));
}

function verify_resident_password(string $input, string $stored): bool
{
    if ($stored === '') {
        return false;
    }

    if (preg_match('/^(\$2y\$|\$argon2)/i', $stored)) {
        return password_verify($input, $stored);
    }

    if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
        return md5($input) === strtolower($stored);
    }

    return hash_equals($stored, $input);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, middle_name, surname, suffix, username, password, account_status, lockout_until, login_attempts FROM residents WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $resident = $result->fetch_assoc();
        $stmt->close();

        if (!$resident) {
            $error = "Invalid username or password.";
        } else {
            $status = strtolower(trim($resident['account_status'] ?? 'active'));
            if ($status !== 'active') {
                $error = "Account status is $status. Please contact the barangay office.";
            } else {
                $lockoutUntil = $resident['lockout_until'] ?? null;
                if (!empty($lockoutUntil) && strtotime($lockoutUntil) > time()) {
                    $error = "Account is temporarily locked. Try again after " . date('M d, Y h:i A', strtotime($lockoutUntil)) . ".";
                } else {
                    $storedPassword = $resident['password'] ?? '';
                    if (verify_resident_password($password, $storedPassword)) {
                        $update = $conn->prepare("UPDATE residents SET last_login = NOW(), login_attempts = 0, lockout_until = NULL WHERE id = ?");
                        $update->bind_param("i", $resident['id']);
                        $update->execute();
                        $update->close();

                        $_SESSION['resident_id'] = $resident['id'];
                        $_SESSION['resident_name'] = build_resident_name($resident, 'full');
                        $_SESSION['resident_username'] = $resident['username'];
                        $_SESSION['user_type'] = 'resident';

                        header("Location: pending_payments.php");
                        exit;
                    }

                    $attempts = intval($resident['login_attempts'] ?? 0) + 1;
                    $lockoutValue = null;
                    if ($attempts >= 5) {
                        $lockoutValue = date('Y-m-d H:i:s', time() + 900);
                        $attempts = 0;
                    }

                    $update = $conn->prepare("UPDATE residents SET login_attempts = ?, lockout_until = ? WHERE id = ?");
                    $update->bind_param("isi", $attempts, $lockoutValue, $resident['id']);
                    $update->execute();
                    $update->close();

                    if ($lockoutValue !== null) {
                        $error = "Too many attempts. Account locked for 15 minutes.";
                    } else {
                        $error = "Invalid username or password.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Login - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="header-banner">
        <h1>RESIDENT PORTAL</h1>
    </div>

    <div class="login-container" style="margin-top: 50px;">
        <h3><i class="fas fa-user"></i> Resident Login</h3>

        <?php if ($notice): ?>
        <div class="success-message">
            <i class="fas fa-info-circle"></i>
            <?= htmlspecialchars($notice) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                    autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required
                        autocomplete="current-password">
                    <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> LOGIN
            </button>
        </form>

        <div style="margin-top: 15px; text-align: center;">
            <a href="../index.php">Back to treasurer login</a>
        </div>
    </div>

    <script src="../assets/js/password-toggle.js"></script>
</body>

</html>
