<?php
include "../../config/database.php";
include "../../config/session.php";

// Get current month and year or from filter
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Operating and Services (from payments + cedula)
$operatingServicesPayments = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE operating_services IS NOT NULL 
    AND operating_services != ''
    AND MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$operatingServicesCedula = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM cedula 
    WHERE MONTH(issued_date) = $month 
    AND YEAR(issued_date) = $year
")->fetch_assoc()['total'] ?? 0;

$operatingServices = $operatingServicesPayments + $operatingServicesCedula;

// Other Collections (from payments + paid pending status)
$otherCollectionsPayments = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
    AND (remarks IS NULL OR remarks NOT LIKE 'Pending Status%')
")->fetch_assoc()['total'] ?? 0;

$pendingPaidCollections = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM payment_status
    WHERE payment_status = 'paid'
    AND MONTH(created_at) = $month
    AND YEAR(created_at) = $year
")->fetch_assoc()['total'] ?? 0;

$pendingPaidBreakdown = [];
$pendingBreakdownResult = $conn->query("
    SELECT certificate_type, COALESCE(SUM(amount), 0) as total
    FROM payment_status
    WHERE payment_status = 'paid'
    AND MONTH(created_at) = $month
    AND YEAR(created_at) = $year
    GROUP BY certificate_type
    ORDER BY certificate_type
");
while ($row = $pendingBreakdownResult->fetch_assoc()) {
    $pendingPaidBreakdown[] = $row;
}

$otherCollections = $otherCollectionsPayments + $pendingPaidCollections;


// Operating & Services breakdown by type (Garbage, Donation, Fines, etc.)
$operatingBreakdown = [];
$breakdownResult = $conn->query("
    SELECT operating_services, COALESCE(SUM(amount), 0) as total
    FROM payments
    WHERE operating_services IS NOT NULL AND operating_services != ''
    AND MONTH(payment_date) = $month AND YEAR(payment_date) = $year
    GROUP BY operating_services
    ORDER BY operating_services
");
while ($row = $breakdownResult->fetch_assoc()) {
    $operatingBreakdown[] = $row;
}

// Other Collections breakdown by service type
$otherCollectionsBreakdown = [];
$otherBreakdownResult = $conn->query("
    SELECT service_type, COALESCE(SUM(amount), 0) as total
    FROM payments
    WHERE MONTH(payment_date) = $month
    AND YEAR(payment_date) = $year
    AND (remarks IS NULL OR remarks NOT LIKE 'Pending Status%')
    GROUP BY service_type
    ORDER BY service_type
");
while ($row = $otherBreakdownResult->fetch_assoc()) {
    $otherCollectionsBreakdown[] = $row;
}

$totalCollections = $operatingServices + $otherCollections;

$monthName = date('F Y', mktime(0, 0, 0, $month, 1, $year));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Collections - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-header {
                display: block !important;
            }
        }

        .print-header {
            display: none;
            text-align: center;
            margin-bottom: 30px;
        }

        .report-section {
            margin-bottom: 30px;
        }

        .report-table {
            width: 100%;
            margin-top: 15px;
        }

        .report-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .report-table td:first-child {
            font-weight: 600;
        }

        .report-table td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .total-row td {
            background: #1F3A93;
            font-size: 18px;
            padding: 15px 10px !important;
            border-top: 2px solid #1F3A93;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar no-print">
            <div class="sidebar-header">
                <img src="../../assets/images/logo.jpg" alt="Barangay Logo"
                    style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../pending_payments/list.php"><i class="fas fa-hourglass-half"></i> Pending Status</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="monthly.php" class="active"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="annual.php"><i class="fas fa-calendar-alt"></i> Annual Report</a></li>
                <li><a href="../change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header no-print">
                <h1><i class="fas fa-chart-line"></i> Statement of Itemized Monthly Collection</h1>
            </div>

            <div class="content-body">
                <!-- Print Header -->
                <div class="print-header">
                    <div
                        style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 20px;">
                        <img src="../../assets/images/logo.jpg" alt="Barangay Logo"
                            style="width: 100px; height: 100px; border-radius: 50%;">
                        <div>
                            <h2 style="color: #1e3a5f; margin-bottom: 5px;">BARANGAY STO. ROSARIO</h2>
                            <p style="color: #666;">Magallanes, Agusan del Norte</p>
                        </div>
                    </div>
                    <h3 style="margin-top: 20px; color: #1e3a5f;">Statement of Itemized Monthly Collection</h3>
                    <p style="color: #666; font-size: 16px;">
                        <?= $monthName ?>
                    </p>
                </div>

                <!-- Filter Section -->
                <div class="card no-print">
                    <div class="card-header">
                        <h3><i class="fas fa-filter"></i> Select Month & Year</h3>
                    </div>
                    <form method="GET" style="display: flex; gap: 15px; align-items: end;">
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label for="month">Month</label>
                            <select id="month" name="month">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option
                                    value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"
                                    <?= $m == $month ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; margin-bottom: 0;">
                            <label for="year">Year</label>
                            <select id="year" name="year">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="flex: 0.5;">
                            <i class="fas fa-search"></i> Generate
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.print()" style="flex: 0.5;">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </form>
                </div>

                <!-- Report Content -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-invoice-dollar"></i>
                            <?= $monthName ?>
                        </h3>
                    </div>

                    <!-- Operating and Services -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #1F3A93;">
                            <i class="fas fa-cogs"></i> OPERATING AND SERVICES
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <?php if (!empty($operatingBreakdown)): ?>
                                <?php foreach ($operatingBreakdown as $bd): ?>
                                <tr>
                                    <td style="padding-left: 30px; color: #555;">
                                        <i class="fas fa-chevron-right" style="font-size:11px; margin-right:6px;"></i>
                                        <?= htmlspecialchars($bd['operating_services']) ?>
                                    </td>
                                    <td>₱<?= number_format($bd['total'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <em>Subtotal – Operating &amp; Services from Payments</em>
                                    </td>
                                    <td>₱<?= number_format($operatingServicesPayments, 2) ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td>Operating and Services from Payments</td>
                                    <td>₱<?= number_format($operatingServicesPayments, 2) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td>Operating and Services from Cedula</td>
                                    <td>₱<?= number_format($operatingServicesCedula, 2) ?>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL OPERATING AND SERVICES</td>
                                    <td>₱<?= number_format($operatingServices, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Other Collections -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #1F3A93;">
                            <i class="fas fa-receipt"></i> OTHER COLLECTIONS
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <?php if (!empty($otherCollectionsBreakdown)): ?>
                                <?php foreach ($otherCollectionsBreakdown as $bd): ?>
                                <tr>
                                    <td style="padding-left: 30px; color: #555;">
                                        <i class="fas fa-chevron-right" style="font-size:11px; margin-right:6px;"></i>
                                        <?= htmlspecialchars($bd['service_type'] ?: 'Unspecified') ?>
                                    </td>
                                    <td>₱<?= number_format($bd['total'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <em>Subtotal – Other Collections from Payments</em>
                                    </td>
                                    <td>₱<?= number_format($otherCollectionsPayments, 2) ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td>Other Collections from Payments</td>
                                    <td>₱<?= number_format($otherCollectionsPayments, 2) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($pendingPaidBreakdown)): ?>
                                <?php foreach ($pendingPaidBreakdown as $bd): ?>
                                <tr>
                                    <td style="padding-left: 30px; color: #555;">
                                        <i class="fas fa-chevron-right" style="font-size:11px; margin-right:6px;"></i>
                                        Paid Status -
                                        <?= htmlspecialchars($bd['certificate_type'] ?: 'Unspecified') ?>
                                    </td>
                                    <td>₱<?= number_format($bd['total'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <em>Subtotal – Pending Status (Paid)</em>
                                    </td>
                                    <td>₱<?= number_format($pendingPaidCollections, 2) ?>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td>Pending Status (Paid)</td>
                                    <td>₱<?= number_format($pendingPaidCollections, 2) ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr class="total-row">
                                    <td>TOTAL OTHER COLLECTIONS</td>
                                    <td>₱<?= number_format($otherCollections, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Grand Total -->
                    <div class="report-section" style="margin-top: 40px;">
                        <table class="report-table">
                            <tbody>
                                <tr style="background: #1F3A93; font-size: 20px;">
                                    <td style="padding: 20px 10px !important; color: #ffffff;">
                                        <i class="fas fa-calculator"></i> TOTAL MONTHLY COLLECTION
                                    </td>
                                    <td style="padding: 20px 10px !important; color: #ffffff;">
                                        ₱<?= number_format($totalCollections, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Signature Section for Print -->
                    <div class="print-header" style="margin-top: 60px; text-align: left;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                            <div>
                                <p style="margin-bottom: 30px;">Prepared by:</p>
                                <p
                                    style="border-bottom: 1px solid #000; display: inline-block; min-width: 200px; margin-bottom: 5px;">
                                </p>
                                <p style="font-weight: 600;">Barangay Treasurer</p>
                            </div>
                            <div>
                                <p style="margin-bottom: 30px;">Approved by:</p>
                                <p
                                    style="border-bottom: 1px solid #000; display: inline-block; min-width: 200px; margin-bottom: 5px;">
                                </p>
                                <p style="font-weight: 600;">Barangay Captain</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../../assets/js/logout-confirm.js"></script>
</body>

</html>