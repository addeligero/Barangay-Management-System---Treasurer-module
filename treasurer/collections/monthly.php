<?php
include "../../config/database.php";
include "../../config/session.php";

// Get current month and year or from filter
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Tax Revenue Collections
$realPropertyTax = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE purpose LIKE '%real property%' 
    AND MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$communityTax = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM cedula 
    WHERE MONTH(issued_date) = $month 
    AND YEAR(issued_date) = $year
")->fetch_assoc()['total'] ?? 0;

$taxRevenue = $realPropertyTax + $communityTax;

// Tax on Goods and Services
$internalRevenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE purpose LIKE '%internal revenue%' 
    AND MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

// Other Collections
$otherCollections = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$totalCollections = $taxRevenue + $internalRevenue + $otherCollections;

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
            background: #fffbea;
            font-size: 18px;
            padding: 15px 10px !important;
            border-top: 2px solid #ffd700;
            color: #1e3a5f;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar no-print">
            <div class="sidebar-header">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="monthly.php" class="active"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
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
                    <h2 style="color: #1e3a5f; margin-bottom: 5px;">BARANGAY STO. ROSARIO</h2>
                    <p style="color: #666;">Magallanes, Agusan del Norte</p>
                    <h3 style="margin-top: 20px; color: #1e3a5f;">Statement of Itemized Monthly Collection</h3>
                    <p style="color: #666; font-size: 16px;">
                        <?= $monthName ?></p>
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
                            <?= $monthName ?></h3>
                    </div>

                    <!-- Tax Revenue Section -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #ffd700;">
                            <i class="fas fa-coins"></i> TAX REVENUE
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Share on Real Property Tax</td>
                                    <td>₱<?= number_format($realPropertyTax, 2) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Community Tax</td>
                                    <td>₱<?= number_format($communityTax, 2) ?>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL TAX REVENUE</td>
                                    <td>₱<?= number_format($taxRevenue, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tax on Goods and Services -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #ffd700;">
                            <i class="fas fa-shopping-cart"></i> TAX ON GOODS AND SERVICES
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Share on Internal Revenue Allotment</td>
                                    <td>₱<?= number_format($internalRevenue, 2) ?>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td>TOTAL TAX ON GOODS AND SERVICES</td>
                                    <td>₱<?= number_format($internalRevenue, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Other Collections -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #ffd700;">
                            <i class="fas fa-receipt"></i> OTHER COLLECTIONS
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Barangay Clearances & Certificates</td>
                                    <td>₱<?= number_format($otherCollections, 2) ?>
                                    </td>
                                </tr>
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
                                <tr style="background: #ffd700; font-size: 20px;">
                                    <td style="padding: 20px 10px !important; color: #1e3a5f;">
                                        <i class="fas fa-calculator"></i> TOTAL MONTHLY COLLECTION
                                    </td>
                                    <td style="padding: 20px 10px !important; color: #1e3a5f;">
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
</body>

</html>