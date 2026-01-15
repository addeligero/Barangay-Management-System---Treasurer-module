<?php
include "../config/database.php";
include "../config/session.php";

// Get statistics
$totalCollection = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total FROM payments
")->fetch_assoc()['total'] ?? 0;

$totalDisbursement = $conn->query("
    SELECT COALESCE(SUM(release_amount), 0) AS total FROM disbursements
")->fetch_assoc()['total'] ?? 0;

$totalCedula = $conn->query("
    SELECT COUNT(*) AS total FROM cedula
")->fetch_assoc()['total'] ?? 0;

$totalBrgyClearance = $conn->query("
    SELECT COUNT(*) AS total FROM payments WHERE service_type = 'Barangay Clearance'
")->fetch_assoc()['total'] ?? 0;

$totalBIR = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total FROM bir_records
")->fetch_assoc()['total'] ?? 0;

// Get recent transactions
$recentPayments = $conn->query("
    SELECT * FROM payments 
    ORDER BY payment_date DESC 
    LIMIT 5
");

$recentDisbursements = $conn->query("
    SELECT * FROM disbursements 
    ORDER BY disburse_date DESC 
    LIMIT 5
");

// Get monthly data for chart
$monthlyData = $conn->query("
    SELECT 
        DATE_FORMAT(payment_date, '%b') as month,
        SUM(amount) as total
    FROM payments
    WHERE YEAR(payment_date) = YEAR(CURDATE())
    GROUP BY MONTH(payment_date)
    ORDER BY MONTH(payment_date)
");

$months = [];
$amounts = [];
while ($row = $monthlyData->fetch_assoc()) {
    $months[] = $row['month'];
    $amounts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer Dashboard - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> Search Payee</a></li>
                <li><a href="payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-tachometer-alt"></i> Treasurer Dashboard</h1>
                <p>Welcome,
                    <?= htmlspecialchars($_SESSION['name']) ?>!
                </p>
            </div>

            <div class="content-body">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4><i class="fas fa-coins"></i> Total Collections</h4>
                        <div class="stat-value">
                            ₱<?= number_format($totalCollection, 2) ?>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <h4><i class="fas fa-money-check-alt"></i> Total Disbursements</h4>
                        <div class="stat-value">
                            ₱<?= number_format($totalDisbursement, 2) ?>
                        </div>
                    </div>
                    <div class="stat-card blue">
                        <h4><i class="fas fa-id-card"></i> Cedula Issued</h4>
                        <div class="stat-value">
                            <?= number_format($totalCedula) ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h4><i class="fas fa-le-certificate"></i> Brgy Clearance Issued</h4>
                        <div class="stat-value">
                            <?= number_format($totalBrgyClearance) ?>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <h4><i class="fas fa-chart-bar"></i> BIR Collections</h4>
                        <div class="stat-value">
                            ₱<?= number_format($totalBIR, 2) ?></div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Monthly Collections Overview</h3>
                    </div>
                    <canvas id="monthlyChart" height="80"></canvas>
                </div>

                <!-- Recent Transactions -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Recent Payments -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-money-bill"></i> Recent Payments</h3>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Purpose</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentPayments->num_rows > 0): ?>
                                    <?php while ($payment = $recentPayments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?>
                                        </td>
                                        <td><?= htmlspecialchars($payment['purpose']) ?>
                                        </td>
                                        <td>₱<?= number_format($payment['amount'], 2) ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center;">No recent payments</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Disbursements -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-hand-holding-usd"></i> Recent Disbursements</h3>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payee</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentDisbursements->num_rows > 0): ?>
                                    <?php while ($disbursement = $recentDisbursements->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($disbursement['disburse_date'])) ?>
                                        </td>
                                        <td><?= htmlspecialchars($disbursement['payee']) ?>
                                        </td>
                                        <td>₱<?= number_format($disbursement['release_amount'], 2) ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center;">No recent disbursements</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Monthly Chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?> ,
                datasets: [{
                    label: 'Monthly Collections',
                    data: <?= json_encode($amounts) ?> ,
                    backgroundColor: '#FFD700',
                    borderColor: '#FFC700',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>