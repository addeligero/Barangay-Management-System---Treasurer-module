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
                <li><a href="../collections/annual.php"><i class="fas fa-calendar-alt"></i> Annual Report</a></li>
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
                        </div>

                        <!-- BIR Percentage Computation (same as BIR add form) -->
                        <div class="card" style="border:1px solid #e0e0e0; padding:20px; border-radius:8px; margin-bottom:20px; background:#fafbff;">
                            <div class="card-header" style="margin-bottom:15px;">
                                <h4 style="margin:0; color:#1e3a5f;"><i class="fas fa-percent"></i> BIR Percentage Computation</h4>
                                <small style="color:#666;">Auto-computes withholding tax; pre-filled from disbursement amount</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="bir_vat_type"><i class="fas fa-tags"></i> VAT Type</label>
                                    <select id="bir_vat_type" name="bir_vat_type" onchange="computeBIR()" style="font-weight:bold; font-size:15px;">
                                        <option value="Non-VAT">Non-VAT (gross × 5%)</option>
                                        <option value="Reg. VAT">Reg. VAT (gross ÷ 1.12 × 6%)</option>
                                    </select>
                                    <small style="color:#666; display:block; margin-top:4px;">Non-VAT: gross × 5% &nbsp;|&nbsp; Reg. VAT: (gross ÷ 1.12) × 6% &rarr; separated into 5% VAT + 1% EWT</small>
                                </div>

                                <div class="form-group">
                                    <label for="bir_gross"><i class="fas fa-money-bill-wave"></i> BIR Gross Amount</label>
                                    <input type="number" id="bir_gross" step="0.01" min="0" placeholder="0.00" oninput="computeBIR()" style="background:#e8f0ff;">
                                    <small style="color:#666;">Pre-filled from disbursement amount; edit if different</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label id="lbl_bir_1pct"><i class="fas fa-percent"></i> 1% Expanded Withholding Tax</label>
                                    <input type="number" id="bir_one_pct" step="0.01" readonly style="background:#f0f4f8;">
                                    <small id="hint_bir_1pct" style="color:#666; display:block; margin-top:4px;">Auto-calculated (1% of base)</small>
                                </div>

                                <div class="form-group">
                                    <label id="lbl_bir_5pct"><i class="fas fa-percent"></i> 5% Withholding Tax</label>
                                    <input type="number" id="bir_five_pct" step="0.01" readonly style="background:#f0f4f8;">
                                    <small id="hint_bir_5pct" style="color:#666; display:block; margin-top:4px;">Auto-calculated (5% of gross)</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-receipt"></i> Total Withholding Tax</label>
                                    <input type="number" id="bir_total_display" step="0.01" readonly
                                        style="background:#1F3A93; color:#fff; font-weight:bold; font-size:16px;">
                                    <small style="color:#666; display:block; margin-top:4px;">1% + 5% total withheld</small>
                                    <!-- hidden field submitted with the form -->
                                    <input type="hidden" id="bir" name="bir">
                                </div>

                                <div class="form-group">
                                    <label><i class="fas fa-hand-holding-usd"></i> Net Amount to Payee</label>
                                    <input type="number" id="bir_net_amount" step="0.01" readonly
                                        style="background:#28a745; color:#fff; font-weight:bold; font-size:18px;">
                                    <small style="color:#666; display:block; margin-top:4px;">Amount released after withholding</small>
                                </div>
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

<script>
    function computeBIR() {
        const gross = parseFloat(document.getElementById('bir_gross').value) || 0;
        const vatType = document.getElementById('bir_vat_type').value;
        let oneP = 0, fiveP = 0;

        if (vatType === 'Reg. VAT') {
            // Reg. VAT: total withholding = (gross ÷ 1.12) × 6%
            // That 6% is composed of 5% VAT withholding + 1% EWT — separate them:
            const base = gross / 1.12;
            oneP  = base * 0.01;   // 1% EWT  (separated from the 6%)
            fiveP = base * 0.05;   // 5% VAT  (the remaining 5% of the 6%)
            document.getElementById('lbl_bir_1pct').innerHTML  = '<i class="fas fa-percent"></i> 1% Expanded Withholding Tax <small style="color:#e74c3c">(separated from 6%)</small>';
            document.getElementById('hint_bir_1pct').textContent = 'Auto-calculated: (gross ÷ 1.12) × 1%  [1% portion of the 6%]';
            document.getElementById('lbl_bir_5pct').innerHTML  = '<i class="fas fa-percent"></i> 5% VAT Withholding <small style="color:#e74c3c">(separated from 6%)</small>';
            document.getElementById('hint_bir_5pct').textContent = 'Auto-calculated: (gross ÷ 1.12) × 5%  [5% portion of the 6%]';
        } else {
            // Non-VAT: gross × 5%, 1% = 0
            fiveP = gross * 0.05;
            oneP  = 0;
            document.getElementById('lbl_bir_1pct').innerHTML  = '<i class="fas fa-percent"></i> 1% Withholding Tax';
            document.getElementById('hint_bir_1pct').textContent = 'N/A for Non-VAT (set to 0)';
            document.getElementById('lbl_bir_5pct').innerHTML  = '<i class="fas fa-percent"></i> 5% Withholding Tax';
            document.getElementById('hint_bir_5pct').textContent = 'Auto-calculated: gross × 5%';
        }

        const total = oneP + fiveP;   // = base×0.06 for Reg.VAT, gross×0.05 for Non-VAT
        const net   = gross - total;
        document.getElementById('bir_one_pct').value       = oneP.toFixed(2);
        document.getElementById('bir_five_pct').value      = fiveP.toFixed(2);
        document.getElementById('bir_total_display').value = total.toFixed(2);
        document.getElementById('bir').value               = total.toFixed(2);
        document.getElementById('bir_net_amount').value    = net.toFixed(2);
    }

    // Pre-fill BIR gross from disbursement amount on input
    document.getElementById('amount').addEventListener('input', function () {
        document.getElementById('bir_gross').value = this.value;
        computeBIR();
    });
</script>