<?php
include "../config/database.php";
include "../config/resident_session.php";

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

$residentId = intval($_SESSION['resident_id']);
$stmt = $conn->prepare("SELECT id, first_name, middle_name, surname, suffix, username, barangay, account_status FROM residents WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $residentId);
$stmt->execute();
$result = $stmt->get_result();
$resident = $result->fetch_assoc();
$stmt->close();

if (!$resident) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=session");
    exit;
}

$status = strtolower(trim($resident['account_status'] ?? 'active'));
if ($status !== 'active') {
    session_unset();
    session_destroy();
    header("Location: login.php?error=account");
    exit;
}

$fullName = build_resident_name($resident, 'full');

$searchQuery = trim($_GET['search'] ?? '');
$searchParam = "%{$searchQuery}%";

$sql = "SELECT * FROM payment_status WHERE payment_status = 'pending' AND resident_id = ?";
$params = [$residentId];
$types = "i";

if ($searchQuery !== '') {
    $sql .= " AND (certificate_type LIKE ? OR purpose LIKE ?)";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

$sql .= " ORDER BY created_at DESC, id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$amountTotal = 0;
$birTotal = 0;

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $amountTotal += floatval($row['amount'] ?? 0);
    $birTotal += floatval($row['bir_tax'] ?? 0);
}

$stmt->close();
$totalCount = count($rows);
$grandTotal = $amountTotal + $birTotal;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments - Resident Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="resident-portal">
    <div class="mobile-topbar">
        <button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Open menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="portal-title">Resident Portal</div>
    </div>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.jpg" alt="Barangay Logo"
                    style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Resident Portal</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="pending_payments.php" class="active"><i class="fas fa-hourglass-half"></i> Pending
                        Payments</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-hourglass-half"></i> Pending Payments</h1>
                <p>Welcome, <?= htmlspecialchars($fullName) ?></p>
            </div>

            <div class="content-body">
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <h4>Pending Items</h4>
                        <div class="stat-value">
                            <?= number_format($totalCount) ?>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <h4>Total Amount</h4>
                        <div class="stat-value">PHP
                            <?= number_format($amountTotal, 2) ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h4>Total BIR Tax</h4>
                        <div class="stat-value">PHP
                            <?= number_format($birTotal, 2) ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header resident-payments-header">
                        <h3><i class="fas fa-list"></i> Your Pending Payments</h3>
                        <form method="GET" action="pending_payments.php" class="resident-search-form">
                            <input type="text" name="search" placeholder="Search certificate or purpose"
                                value="<?= htmlspecialchars($searchQuery) ?>"
                                class="resident-search-input">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </form>
                    </div>

                    <div style="margin-bottom: 15px; color: #4a5568; font-size: 14px;">
                        Showing records matched to your resident name. If you see missing items, please contact the
                        treasurer.
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Certificate Type</th>
                                    <th>Purpose</th>
                                    <th>Amount</th>
                                    <th>BIR Tax</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($totalCount > 0): ?>
                                <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td><span
                                            class="badge badge-info"><?= htmlspecialchars($row['certificate_type']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['purpose']) ?>
                                    </td>
                                    <td>PHP
                                        <?= number_format($row['amount'], 2) ?>
                                    </td>
                                    <td>PHP
                                        <?= number_format($row['bir_tax'], 2) ?>
                                    </td>
                                    <td><strong>PHP
                                            <?= number_format($row['amount'] + $row['bir_tax'], 2) ?></strong>
                                    </td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                                        <p style="margin-top: 15px; color: #999;">No pending payments found</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Resident Details</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                        <div><strong>Name:</strong>
                            <?= htmlspecialchars($fullName) ?>
                        </div>
                        <div><strong>Username:</strong>
                            <?= htmlspecialchars($resident['username']) ?>
                        </div>
                        <div><strong>Barangay:</strong>
                            <?= htmlspecialchars($resident['barangay'] ?? 'N/A') ?>
                        </div>
                        <div><strong>Matched Total:</strong> PHP
                            <?= number_format($grandTotal, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');

        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>

</html>