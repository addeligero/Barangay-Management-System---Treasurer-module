<?php
include "../../config/database.php";
include "../../config/session.php";

$birId = intval($_GET['id'] ?? 0);
if ($birId <= 0) {
    header("Location: list.php?error=Invalid BIR record ID.");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM bir_records WHERE id = ?");
$stmt->bind_param("i", $birId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: list.php?error=BIR record not found.");
    exit;
}
$bir = $result->fetch_assoc();
$stmt->close();

$error = $_GET['error'] ?? '';
$recordDate = !empty($bir['record_date']) ? date('Y-m-d', strtotime($bir['record_date'])) : date('Y-m-d');
$grossAmount = number_format((float) $bir['gross_amount'], 2, '.', '');
$onePercent = number_format((float) $bir['one_percent'], 2, '.', '');
$fivePercent = number_format((float) $bir['five_percent'], 2, '.', '');
$netAmount = number_format((float) $bir['net_amount'], 2, '.', '');
$totalTax = number_format((float) $bir['total_amount'], 2, '.', '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit BIR Record - Barangay Sto. Rosario</title>
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
                <li><a href="../pending_payments/list.php"><i class="fas fa-hourglass-half"></i> Pending Status</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../collections/annual.php"><i class="fas fa-calendar-alt"></i> Annual Report</a></li>
                <li><a href="../change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-percent"></i> Edit BIR Record</h1>
            </div>

            <div class="content-body">
                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calculator"></i> BIR Percentage Computation</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Update 1% and 5% withholding tax</p>
                    </div>

                    <form method="POST" action="save.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id"
                            value="<?= $birId ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tin"><i class="fas fa-id-card"></i> TIN (Tax Identification Number)
                                    *</label>
                                <input type="text" id="tin" name="tin"
                                    value="<?= htmlspecialchars($bir['tin']) ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="record_date"><i class="fas fa-calendar"></i> Record Date *</label>
                                <input type="date" id="record_date" name="record_date"
                                    value="<?= htmlspecialchars($recordDate) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="payee"><i class="fas fa-building"></i> Payee Name *</label>
                                <input type="text" id="payee" name="payee"
                                    value="<?= htmlspecialchars($bir['payee']) ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="vat_type"><i class="fas fa-tags"></i> VAT Type *</label>
                                <select id="vat_type" name="vat_type" required onchange="computeNet()"
                                    style="font-weight: bold; font-size: 15px;">
                                    <option value="Non-VAT Supplies" <?= $bir['vat_type'] === 'Non-VAT Supplies' ? 'selected' : '' ?>>Non-VAT
                                        Supplies (gross × 1% and 3%)</option>
                                    <option value="Non-VAT Services" <?= $bir['vat_type'] === 'Non-VAT Services' ? 'selected' : '' ?>>Non-VAT
                                        Services (gross × 2% and 3%)</option>
                                    <option value="Reg. VAT" <?= $bir['vat_type'] === 'Reg. VAT' ? 'selected' : '' ?>>Reg.
                                        VAT (gross ÷ 1.12 × 6%)</option>
                                </select>
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Non-VAT Supplies: gross × 1% and 3% &nbsp;|&nbsp; Non-VAT Services: gross × 2% and
                                    3% &nbsp;|&nbsp; Reg. VAT: (gross ÷ 1.12) × 6% &rarr; separated into 5% VAT + 1% EWT
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="gross_amount"><i class="fas fa-money-bill-wave"></i> Gross Amount (Before Tax)
                                *</label>
                            <input type="number" id="gross_amount" name="gross_amount" step="0.01" min="0"
                                value="<?= htmlspecialchars($grossAmount) ?>"
                                required oninput="computeNet()"
                                style="background: #e8f0ff; font-weight: bold; font-size: 16px;">
                            <small style="color: #666; display: block; margin-top: 5px;">Enter the amount before
                                withholding tax deduction</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label id="label_one_percent" for="one_percent"><i class="fas fa-percent"></i> 1%
                                    Expanded Withholding Tax</label>
                                <input type="number" id="one_percent" name="one_percent" step="0.01" min="0"
                                    value="<?= htmlspecialchars($onePercent) ?>"
                                    readonly style="background: #f0f4f8;">
                                <small id="hint_one_percent"
                                    style="color: #666; display: block; margin-top: 5px;">Auto-calculated (1% of
                                    base)</small>
                            </div>

                            <div class="form-group">
                                <label id="label_five_percent" for="five_percent"><i class="fas fa-percent"></i> 5%
                                    Withholding Tax</label>
                                <input type="number" id="five_percent" name="five_percent" step="0.01" min="0"
                                    value="<?= htmlspecialchars($fivePercent) ?>"
                                    readonly style="background: #f0f4f8;">
                                <small id="hint_five_percent"
                                    style="color: #666; display: block; margin-top: 5px;">Auto-calculated (5% of
                                    gross)</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_tax"><i class="fas fa-receipt"></i> Total Withholding Tax</label>
                                <input type="number" id="total_tax" step="0.01"
                                    value="<?= htmlspecialchars($totalTax) ?>"
                                    readonly
                                    style="background: #1F3A93; color: #ffffff; font-weight: bold; font-size: 16px;">
                                <small style="color: #666; display: block; margin-top: 5px;">1% + 5% total
                                    withheld</small>
                            </div>

                            <div class="form-group">
                                <label for="net_amount"><i class="fas fa-hand-holding-usd"></i> Net Amount to
                                    Payee</label>
                                <input type="number" id="net_amount" name="net_amount" step="0.01"
                                    value="<?= htmlspecialchars($netAmount) ?>"
                                    readonly
                                    style="background: #28a745; color: #ffffff; font-weight: bold; font-size: 18px;">
                                <small style="color: #666; display: block; margin-top: 5px;">Amount released after
                                    withholding</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="remarks"><i class="fas fa-comment"></i> Remarks/Notes</label>
                            <textarea id="remarks" name="remarks" rows="3"
                                placeholder="Enter any additional notes..."><?= htmlspecialchars($bir['remarks'] ?? '') ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="list.php" class="btn btn-secondary"
                                style="flex: 1; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function computeNet() {
            const gross = parseFloat(document.getElementById('gross_amount').value) || 0;
            const vatType = document.getElementById('vat_type').value;
            let onePercent = 0,
                fivePercent = 0;

            if (vatType === 'Reg. VAT') {
                const base = gross / 1.12;
                onePercent = base * 0.01;
                fivePercent = base * 0.05;
                document.getElementById('label_one_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 1% Expanded Withholding Tax ';
                document.getElementById('hint_one_percent').textContent =
                    'Auto-calculated: (gross ÷ 1.12) × 1%  [1% portion of the 6%]';
                document.getElementById('label_five_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 5% VAT Withholding';
                document.getElementById('hint_five_percent').textContent =
                    'Auto-calculated: (gross ÷ 1.12) × 5%  [5% portion of the 6%]';
            } else if (vatType === 'Non-VAT Supplies') {
                onePercent = gross * 0.01;
                fivePercent = gross * 0.03;
                document.getElementById('label_one_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 1% Withholding Tax';
                document.getElementById('hint_one_percent').textContent = 'Auto-calculated: gross × 1%';
                document.getElementById('label_five_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 3% Withholding Tax';
                document.getElementById('hint_five_percent').textContent = 'Auto-calculated: gross × 3%';
            } else {
                onePercent = gross * 0.02;
                fivePercent = gross * 0.03;
                document.getElementById('label_one_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 2% Withholding Tax';
                document.getElementById('hint_one_percent').textContent = 'Auto-calculated: gross × 2%';
                document.getElementById('label_five_percent').innerHTML =
                    '<i class="fas fa-percent"></i> 3% Withholding Tax';
                document.getElementById('hint_five_percent').textContent = 'Auto-calculated: gross × 3%';
            }

            const totalTax = onePercent + fivePercent;
            const netAmount = gross - totalTax;

            document.getElementById('one_percent').value = onePercent.toFixed(2);
            document.getElementById('five_percent').value = fivePercent.toFixed(2);
            document.getElementById('total_tax').value = totalTax.toFixed(2);
            document.getElementById('net_amount').value = netAmount.toFixed(2);
        }

        computeNet();

        (function() {
            const forms = Array.from(document.querySelectorAll('form'));
            if (!forms.length) {
                return;
            }

            function serializeForm(form) {
                const data = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of data.entries()) {
                    params.append(key, value);
                }
                return params.toString();
            }

            const formSnapshots = new Map();
            forms.forEach((form) => {
                formSnapshots.set(form, serializeForm(form));
                form.addEventListener('submit', () => {
                    form.dataset.submitting = 'true';
                });
            });

            window.addEventListener('beforeunload', function(event) {
                const hasUnsaved = forms.some((form) => {
                    if (form.dataset.submitting === 'true') {
                        return false;
                    }
                    return serializeForm(form) !== formSnapshots.get(form);
                });

                if (!hasUnsaved) {
                    return;
                }

                event.preventDefault();
                event.returnValue = '';
            });
        })();
    </script>
</body>

</html>