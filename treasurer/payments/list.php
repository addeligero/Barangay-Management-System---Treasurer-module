<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM payments ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments List - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logo.jpg" alt="Barangay Logo" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../search.php"><i class="fas fa-search"></i> Search Payee</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-money-bill-wave"></i> Payment Records</h1>
            </div>

            <div class="content-body">
                <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Payment recorded successfully!
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-list"></i> All Payments</h3>
                        <a href="add.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> New Payment
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt #</th>
                                    <th>Payer</th>
                                    <th>Service</th>
                                    <th>Purpose</th>
                                    <th>Amount</th>
                                    <th>BIR Tax</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= isset($row['payment_date']) ? date('M d, Y', strtotime($row['payment_date'])) : date('M d, Y') ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['receipt_no']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($row['payer_name']) ?>
                                    </td>
                                    <td><span
                                            class="badge badge-info"><?= htmlspecialchars($row['service_type']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['purpose']) ?>
                                    </td>
                                    <td>₱<?= number_format($row['amount'], 2) ?>
                                    </td>
                                    <td>₱<?= number_format($row['bir_tax'], 2) ?>
                                    </td>
                                    <td><strong>₱<?= number_format($row['amount'] + $row['bir_tax'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-secondary"
                                                onclick="viewPayment(<?= $row['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger"
                                                onclick="deletePayment(<?= $row['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                                        <p style="margin-top: 15px; color: #999;">No payment records found</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function viewPayment(id) {
            alert('View payment details for ID: ' + id);
        }

        function deletePayment(id) {
            if (confirm('Are you sure you want to delete this payment record?')) {
                window.location.href = 'save.php?action=delete&id=' + id;
            }
        }
    </script>
</body>

</html>