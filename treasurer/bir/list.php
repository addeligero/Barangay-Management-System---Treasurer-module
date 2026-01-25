<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM bir_records ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIR Records - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logo.jpg" alt="Barangay Logo"
                    style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../search.php"><i class="fas fa-search"></i> Search Payee</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-percent"></i> BIR Percentage Records</h1>
            </div>

            <div class="content-body">
                <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> BIR record saved successfully!
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-list"></i> All BIR Records</h3>
                        <a href="add.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> New BIR Record
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>TIN</th>
                                    <th>Payee</th>
                                    <th>Gross Amount</th>
                                    <th>Net Amount</th>
                                    <th>1%</th>
                                    <th>5%</th>
                                    <th>Total Tax</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                            $grossAmount = $row['gross_amount'];
                                    $onePercent = $row['one_percent'];
                                    $fivePercent = $row['five_percent'];
                                    $netAmount = $row['net_amount'];
                                    $totalTax = $onePercent + $fivePercent;
                                    ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['tin']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($row['payee']) ?>
                                    </td>
                                    <td>₱<?= number_format($grossAmount, 2) ?>
                                    </td>
                                    <td>₱<?= number_format($netAmount, 2) ?>
                                    </td>
                                    <td>₱<?= number_format($onePercent, 2) ?>
                                    </td>
                                    <td>₱<?= number_format($fivePercent, 2) ?>
                                    </td>
                                    <td><strong>₱<?= number_format($totalTax, 2) ?></strong>
                                    </td>
                                    <td>
                                        <div class="action-buttons">

                                            <button class="btn btn-sm btn-danger"
                                                onclick="deleteBIR(<?= $row['id'] ?>)">
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
                                        <p style="margin-top: 15px; color: #999;">No BIR records found</p>
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
        function viewBIR(id) {
            alert('View BIR record ID: ' + id);
        }

        function deleteBIR(id) {
            if (confirm('Are you sure you want to delete this BIR record?')) {
                window.location.href = 'save.php?action=delete&id=' + id;
            }
        }
    </script>
</body>

</html>