<?php
include "../../config/database.php";
include "../../config/session.php";

// Handle manual entry save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_manual'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $entryName = trim($_POST['entry_name']);
    $amount = floatval($_POST['amount']);
    $entryType = $_POST['entry_type'] ?? '';
    
    if (!empty($entryName)) {
        $stmt = $conn->prepare("
            INSERT INTO monthly_manual_entries (month, year, entry_name, amount, entry_type)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            amount = VALUES(amount),
            entry_type = VALUES(entry_type)
        ");
        $stmt->bind_param("iisds", $month, $year, $entryName, $amount, $entryType);
        $stmt->execute();
    }
    
    header("Location: monthly.php?month=" . str_pad($month, 2, '0', STR_PAD_LEFT) . "&year=$year&saved=1");
    exit;
}

// Handle manual entry delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_entry'])) {
    $entryId = intval($_POST['entry_id']);
    $month = $_POST['month'];
    $year = $_POST['year'];
    
    $stmt = $conn->prepare("DELETE FROM monthly_manual_entries WHERE id = ?");
    $stmt->bind_param("i", $entryId);
    $stmt->execute();
    
    header("Location: monthly.php?month=" . str_pad($month, 2, '0', STR_PAD_LEFT) . "&year=$year&deleted=1");
    exit;
}

// Get current month and year or from filter
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get all manual entries for this month/year
$manualEntries = [];
$result = $conn->query("
    SELECT id, entry_name, amount, entry_type
    FROM monthly_manual_entries
    WHERE month = $month AND year = $year
    ORDER BY entry_name
");
while ($row = $result->fetch_assoc()) {
    $manualEntries[$row['entry_name']] = $row;
}

// Group manual entries by category
$taxRevenueEntries = [];
$taxGoodsServicesEntries = [];
$operatingServicesEntries = [];
$otherCollectionsEntries = [];

foreach ($manualEntries as $entry) {
    switch ($entry['entry_type']) {
        case 'Tax Revenue':
            $taxRevenueEntries[] = $entry;
            break;
        case 'Tax on Goods & Services':
            $taxGoodsServicesEntries[] = $entry;
            break;
        case 'Operating & Services':
            $operatingServicesEntries[] = $entry;
            break;
        case 'Other':
            $otherCollectionsEntries[] = $entry;
            break;
    }
}

// Calculate Tax Revenue (from payments + manual entries)
$realPropertyTax = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE purpose LIKE '%real property%' 
    AND MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$taxRevenueManual = array_sum(array_column($taxRevenueEntries, 'amount'));
$taxRevenue = $realPropertyTax + $taxRevenueManual;

// Calculate Tax on Goods and Services (from payments + manual entries)
$internalRevenue = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE purpose LIKE '%internal revenue%' 
    AND MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$taxGoodsServicesManual = array_sum(array_column($taxGoodsServicesEntries, 'amount'));
$taxGoodsServicesTotal = $internalRevenue + $taxGoodsServicesManual;

// Operating and Services (from payments + cedula + manual entries)
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

$operatingServicesManual = array_sum(array_column($operatingServicesEntries, 'amount'));
$operatingServices = $operatingServicesPayments + $operatingServicesCedula + $operatingServicesManual;

// Other Collections (from payments + manual entries)
$otherCollectionsPayments = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM payments 
    WHERE MONTH(payment_date) = $month 
    AND YEAR(payment_date) = $year
")->fetch_assoc()['total'] ?? 0;

$otherCollectionsManual = array_sum(array_column($otherCollectionsEntries, 'amount'));
$otherCollections = $otherCollectionsPayments + $otherCollectionsManual;

$totalCollections = $taxRevenue + $taxGoodsServicesTotal + $operatingServices + $otherCollections;

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
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Treasurer Module</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../search.php"><i class="fas fa-search"></i> Search Payee</a></li>
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
                <?php if (isset($_GET['saved'])): ?>
                <div class="card" style="background: #d4edda; border-left: 5px solid #28a745; margin-bottom: 20px;">
                    <div style="padding: 15px; color: #155724;">
                        <i class="fas fa-check-circle"></i> <strong>Manual entry saved successfully!</strong>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                <div class="card" style="background: #f8d7da; border-left: 5px solid #dc3545; margin-bottom: 20px;">
                    <div style="padding: 15px; color: #721c24;">
                        <i class="fas fa-trash-alt"></i> <strong>Manual entry deleted successfully!</strong>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Manual Entry Management -->
                <div class="card no-print">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> Manage Manual Entries</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Add custom entries for manually
                            inputted values (e.g., Real Property Tax, Internal Revenue Allotment, etc.)</p>
                    </div>

                    <!-- Existing Manual Entries -->
                    <?php if (!empty($manualEntries)): ?>
                    <div style="margin: 20px; overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Entry Name</th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600;">Amount</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Type</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600; width: 100px;">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($manualEntries as $entry): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px;">
                                        <?= htmlspecialchars($entry['entry_name']) ?>
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: 500;">
                                        ₱<?= number_format($entry['amount'], 2) ?>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <?php if ($entry['entry_type']): ?>
                                        <span
                                            style="background: #e7f3ff; color: #004085; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                            <?= htmlspecialchars($entry['entry_type']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Delete this entry?');">
                                            <input type="hidden" name="entry_id"
                                                value="<?= $entry['id'] ?>">
                                            <input type="hidden" name="month"
                                                value="<?= $month ?>">
                                            <input type="hidden" name="year"
                                                value="<?= $year ?>">
                                            <input type="hidden" name="delete_entry" value="1">
                                            <button type="submit"
                                                style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Add New Entry Form -->
                    <form method="POST"
                        style="margin: 20px; display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end; background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <input type="hidden" name="month"
                            value="<?= $month ?>">
                        <input type="hidden" name="year"
                            value="<?= $year ?>">
                        <input type="hidden" name="save_manual" value="1">

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="entry_name"><i class="fas fa-tag"></i> Entry Name</label>
                            <input type="text" id="entry_name" name="entry_name" placeholder="e.g., Real Property Tax"
                                required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="amount"><i class="fas fa-peso-sign"></i> Amount (₱)</label>
                            <input type="number" id="amount" name="amount" step="0.01" placeholder="0.00" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="entry_type"><i class="fas fa-list"></i> Category (Optional)</label>
                            <select id="entry_type" name="entry_type">
                                <option value="">- Select -</option>
                                <option value="Tax Revenue">Tax Revenue</option>
                                <option value="Tax on Goods & Services">Tax on Goods & Services</option>
                                <option value="Operating & Services">Operating & Services</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                            <i class="fas fa-plus"></i> Add Entry
                        </button>
                    </form>
                </div>

                <!-- Print Header -->
                <div class="print-header">
                    <h2 style="color: #1e3a5f; margin-bottom: 5px;">BARANGAY STO. ROSARIO</h2>
                    <p style="color: #666;">Magallanes, Agusan del Norte</p>
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

                    <!-- Tax Revenue Section -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #1F3A93;">
                            <i class="fas fa-coins"></i> TAX REVENUE
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Share on Real Property Tax</td>
                                    <td>₱<?= number_format($realPropertyTax, 2) ?>
                                    </td>
                                </tr>
                                <?php foreach ($taxRevenueEntries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['entry_name']) ?>
                                    </td>
                                    <td>₱<?= number_format($entry['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #1F3A93;">
                            <i class="fas fa-shopping-cart"></i> TAX ON GOODS AND SERVICES
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Share on Internal Revenue Allotment</td>
                                    <td>₱<?= number_format($internalRevenue, 2) ?>
                                    </td>
                                </tr>
                                <?php foreach ($taxGoodsServicesEntries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['entry_name']) ?>
                                    </td>
                                    <td>₱<?= number_format($entry['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td>TOTAL TAX ON GOODS AND SERVICES</td>
                                    <td>₱<?= number_format($taxGoodsServicesTotal, 2) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Operating and Services -->
                    <div class="report-section">
                        <h4
                            style="color: #1e3a5f; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid #1F3A93;">
                            <i class="fas fa-cogs"></i> OPERATING AND SERVICES
                        </h4>
                        <table class="report-table">
                            <tbody>
                                <tr>
                                    <td>Operating and Services from Payments</td>
                                    <td>₱<?= number_format($operatingServicesPayments, 2) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Operating and Services from Cedula</td>
                                    <td>₱<?= number_format($operatingServicesCedula, 2) ?>
                                    </td>
                                </tr>
                                <?php foreach ($operatingServicesEntries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['entry_name']) ?>
                                    </td>
                                    <td>₱<?= number_format($entry['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                                <tr>
                                    <td>Barangay Clearances & Certificates</td>
                                    <td>₱<?= number_format($otherCollectionsPayments, 2) ?>
                                    </td>
                                </tr>
                                <?php foreach ($otherCollectionsEntries as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['entry_name']) ?>
                                    </td>
                                    <td>₱<?= number_format($entry['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
</body>

</html>