<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("
    SELECT * FROM payment_status
    WHERE payment_status = 'pending'
    ORDER BY created_at DESC, id DESC
");

$success = "";
if (isset($_GET['paid'])) {
    $success = "Pending payment marked as paid.";
} elseif (isset($_GET['updated'])) {
    $success = "Pending payment updated.";
}
$error = isset($_GET['error']) ? $_GET['error'] : "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Status - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
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
                <li><a href="list.php" class="active"><i class="fas fa-hourglass-half"></i> Pending Status</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../collections/annual.php"><i class="fas fa-calendar-alt"></i> Annual Report</a></li>
                <li><a href="../change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-hourglass-half"></i> Pending Payment Status</h1>
            </div>

            <div class="content-body">
                <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-list"></i> Pending Payments</h3>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Resident</th>
                                    <th>Certificate Type</th>
                                    <th>Purpose</th>
                                    <th>Amount</th>
                                    <th>BIR Tax</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['resident_fname']) ?>
                                    </td>
                                    <td><span
                                            class="badge badge-info"><?= htmlspecialchars($row['certificate_type']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['purpose']) ?>
                                    </td>
                                    <td>₱<?= number_format($row['amount'], 2) ?>
                                    </td>
                                    <td>₱<?= number_format($row['bir_tax'], 2) ?>
                                    </td>
                                    <td><strong>₱<?= number_format($row['amount'] + $row['bir_tax'], 2) ?></strong>
                                    </td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="btn btn-sm btn-secondary" href="edit.php?id=<?= $row['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success"
                                                onclick="confirmPaid(<?= $row['id'] ?>, this)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                                        <p style="margin-top: 15px; color: #999;">No pending payments found</p>
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

    <!-- Mark Paid Confirmation Modal -->
    <div id="paidModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 48px;"></i>
                <h2>Confirm Mark as Paid</h2>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure?</strong> This will move the record to Payments.</p>
                <p id="paidDetails"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closePaidModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-success" id="confirmPaidBtn">
                    <i class="fas fa-check"></i> Yes, Mark Paid
                </button>
            </div>
        </div>
    </div>

    <form id="markPaidForm" method="POST" action="save.php" style="display: none;">
        <input type="hidden" name="action" value="mark_paid">
        <input type="hidden" name="id" id="markPaidId">
    </form>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        .modal-header {
            padding: 30px;
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            margin: 15px 0 0 0;
            color: #333;
            font-size: 24px;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-body p {
            margin: 10px 0;
            line-height: 1.6;
        }

        .modal-footer {
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 2px solid #f0f0f0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <script>
        let markPaidId = null;

        function confirmPaid(id, button) {
            markPaidId = id;
            const row = button.closest('tr');
            const resident = row.cells[1].textContent.trim();
            const certificateType = row.cells[2].textContent.trim();
            const amount = row.cells[4].textContent.trim();

            document.getElementById('paidDetails').innerHTML =
                `<strong>Resident:</strong> ${resident}<br>` +
                `<strong>Certificate:</strong> ${certificateType}<br>` +
                `<strong>Amount:</strong> ${amount}`;

            document.getElementById('paidModal').style.display = 'flex';
        }

        function closePaidModal() {
            document.getElementById('paidModal').style.display = 'none';
            markPaidId = null;
        }

        document.getElementById('confirmPaidBtn').addEventListener('click', function() {
            if (markPaidId) {
                document.getElementById('markPaidId').value = markPaidId;
                document.getElementById('markPaidForm').submit();
            }
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('paidModal');
            if (event.target === modal) {
                closePaidModal();
            }
        });
    </script>
</body>

</html>