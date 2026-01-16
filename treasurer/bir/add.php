<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add BIR Record - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
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
                <h1><i class="fas fa-percent"></i> New BIR Record</h1>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calculator"></i> BIR Percentage Computation</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Calculate 1% and 5% withholding tax</p>
                    </div>

                    <form method="POST" action="save.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tin"><i class="fas fa-id-card"></i> TIN (Tax Identification Number) *</label>
                                <input type="text" id="tin" name="tin" placeholder="000-000-000-000" required>
                            </div>

                            <div class="form-group">
                                <label for="record_date"><i class="fas fa-calendar"></i> Record Date *</label>
                                <input type="date" id="record_date" name="record_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="payee"><i class="fas fa-building"></i> Payee Name *</label>
                            <input type="text" id="payee" name="payee" placeholder="e.g., Uncle Ben Meatshop" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="gross_amount"><i class="fas fa-money-bill"></i> Gross Amount *</label>
                                <input type="number" id="gross_amount" name="gross_amount" step="0.01" min="0" placeholder="0.00" required oninput="computeTax()">
                            </div>

                            <div class="form-group">
                                <label for="base_amount"><i class="fas fa-calculator"></i> Base Amount *</label>
                                <input type="number" id="base_amount" name="base_amount" step="0.01" min="0" placeholder="0.00" required oninput="computeTax()">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="one_percent"><i class="fas fa-percent"></i> 1% Withholding Tax</label>
                                <input type="number" id="one_percent" name="one_percent" step="0.01" readonly style="background: #fffbea; font-weight: bold;">
                            </div>

                            <div class="form-group">
                                <label for="five_percent"><i class="fas fa-percent"></i> 5% Withholding Tax</label>
                                <input type="number" id="five_percent" name="five_percent" step="0.01" readonly style="background: #fffbea; font-weight: bold;">
                            </div>

                            <div class="form-group">
                                <label for="total_tax"><i class="fas fa-dollar-sign"></i> Total Tax</label>
                                <input type="number" id="total_tax" step="0.01" readonly style="background: #ffd700; font-weight: bold; font-size: 18px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="remarks"><i class="fas fa-comment"></i> Remarks/Notes</label>
                            <textarea id="remarks" name="remarks" rows="3" placeholder="Enter any additional notes..."></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Save BIR Record
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

    <script>
        function computeTax() {
            const baseAmount = parseFloat(document.getElementById('base_amount').value) || 0;
            
            const onePercent = baseAmount * 0.01;
            const fivePercent = baseAmount * 0.05;
            const totalTax = onePercent + fivePercent;
            
            document.getElementById('one_percent').value = onePercent.toFixed(2);
            document.getElementById('five_percent').value = fivePercent.toFixed(2);
            document.getElementById('total_tax').value = totalTax.toFixed(2);
        }
    </script>
</body>
</html>
