<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM cedula ORDER BY issued_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cedula Records - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-id-card"></i> Cedula (Community Tax Certificate)</h1>
            </div>

            <div class="content-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> Cedula issued successfully!
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-list"></i> All Cedula Records</h3>
                        <a href="add.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Issue New Cedula
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date Issued</th>
                                    <th>Cedula #</th>
                                    <th>Full Name</th>
                                    <th>Address</th>
                                    <th>Age</th>
                                    <th>Occupation</th>
                                    <th>TIN</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($row['issued_date'])) ?></td>
                                            <td><strong><?= htmlspecialchars($row['cedula_no'] ?? 'N/A') ?></strong></td>
                                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                                            <td><?= htmlspecialchars($row['address']) ?></td>
                                            <td><?= $row['age'] ?></td>
                                            <td><?= htmlspecialchars($row['occupation']) ?></td>
                                            <td><?= htmlspecialchars($row['tin'] ?? 'N/A') ?></td>
                                            <td><strong>₱<?= number_format($row['amount'], 2) ?></strong></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-secondary" onclick="viewCedula(<?= $row['id'] ?>)">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteCedula(<?= $row['id'] ?>)">
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
                                            <p style="margin-top: 15px; color: #999;">No cedula records found</p>
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
        function viewCedula(id) {
            alert('Print cedula ID: ' + id);
        }

        function deleteCedula(id) {
            if (confirm('Are you sure you want to delete this cedula record?')) {
                window.location.href = 'save.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>
