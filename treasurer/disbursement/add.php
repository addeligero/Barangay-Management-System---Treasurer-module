<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Disbursement - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/logo.jpg" alt="Barangay Logo" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../search.php"><i class="fas fa-search"></i> Search Payee</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-hand-holding-usd"></i> Record New Disbursement</h1>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-invoice-dollar"></i> Disbursement Information</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Complete all required disbursement details</p>
                    </div>

                    <form method="POST" action="save.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="disburse_date"><i class="fas fa-calendar"></i> Date *</label>
                                <input type="date" id="disburse_date" name="date" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="check_no"><i class="fas fa-money-check"></i> CH CH # (Check Number) *</label>
                                <input type="text" id="check_no" name="check_no" placeholder="e.g., 724747" required>
                            </div>

                            <div class="form-group">
                                <label for="dv_no"><i class="fas fa-file-alt"></i> DV No. *</label>
                                <input type="text" id="dv_no" name="dv_no" placeholder="e.g., 001" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="payee"><i class="fas fa-user"></i> Payee Name *</label>
                            <input type="text" id="payee" name="payee" placeholder="Enter payee name" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="amount"><i class="fas fa-money-bill"></i> Amount *</label>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="0.00" required>
                            </div>

                            <div class="form-group">
                                <label for="fund"><i class="fas fa-piggy-bank"></i> Fund *</label>
                                <input type="text" id="fund" name="fund" placeholder="e.g., SK 10%, General Fund" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="payroll"><i class="fas fa-users"></i> Payroll</label>
                                <input type="text" id="payroll" name="payroll" placeholder="Enter payroll details (optional)">
                            </div>

                            <div class="form-group">
                                <label for="bir"><i class="fas fa-percent"></i> BIR</label>
                                <input type="text" id="bir" name="bir" placeholder="BIR details (optional)">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="purpose"><i class="fas fa-info-circle"></i> Particular/Purpose *</label>
                            <textarea id="purpose" name="purpose" rows="3" placeholder="e.g., Cable service, Office supplies, Salary" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="release_amount"><i class="fas fa-hand-holding-usd"></i> Release Amount *</label>
                            <input type="number" id="release_amount" name="release" step="0.01" min="0" placeholder="0.00" required>
                            <small style="color: #666;">Actual amount released/paid</small>
                        </div>

                        <div class="form-group">
                            <label for="remarks"><i class="fas fa-comment"></i> Remarks</label>
                            <textarea id="remarks" name="remarks" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Save Disbursement
                            </button>
                            <a href="list.php" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
