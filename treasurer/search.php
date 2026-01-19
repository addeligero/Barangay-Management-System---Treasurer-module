<?php
include "../config/database.php";
include "../config/session.php";

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$results = [];

if (!empty($searchQuery)) {
    $searchParam = "%{$searchQuery}%";
    
    // Search in payments
    $paymentStmt = $conn->prepare("
        SELECT 
            'Payment' as type, 
            receipt_no as reference, 
            payer_name as name, 
            purpose as description, 
            amount, 
            payment_date as date, 
            '' as address, 
            '' as contact_number, 
            '' as cedula_num, 
            '' as bir_type,
            '' as tin,
            '' as sex,
            '' as civil_status,
            '' as occupation,
            service_type
        FROM payments 
        WHERE payer_name LIKE ? 
        ORDER BY payment_date DESC
    ");
    $paymentStmt->bind_param("s", $searchParam);
    $paymentStmt->execute();
    $paymentResult = $paymentStmt->get_result();
    
    while ($row = $paymentResult->fetch_assoc()) {
        $results[] = $row;
    }
    
    // Search in cedula
    $cedulaStmt = $conn->prepare("
        SELECT 
            'Cedula' as type, 
            cedula_no as reference, 
            full_name as name, 
            'Community Tax Certificate' as description, 
            amount, 
            issued_date as date, 
            address, 
            '' as contact_number, 
            cedula_no as cedula_num, 
            '' as bir_type,
            tin,
            sex,
            civil_status,
            occupation,
            '' as service_type
        FROM cedula 
        WHERE full_name LIKE ? 
        ORDER BY issued_date DESC
    ");
    $cedulaStmt->bind_param("s", $searchParam);
    $cedulaStmt->execute();
    $cedulaResult = $cedulaStmt->get_result();
    
    while ($row = $cedulaResult->fetch_assoc()) {
        $results[] = $row;
    }
    
    // Search in disbursements
    $disbursementStmt = $conn->prepare("
        SELECT 
            'Disbursement' as type, 
            check_no as reference, 
            payee as name, 
            purpose as description, 
            release_amount as amount, 
            disburse_date as date, 
            '' as address, 
            '' as contact_number, 
            '' as cedula_num, 
            '' as bir_type,
            '' as tin,
            '' as sex,
            '' as civil_status,
            '' as occupation,
            '' as service_type
        FROM disbursements 
        WHERE payee LIKE ? 
        ORDER BY disburse_date DESC
    ");
    $disbursementStmt->bind_param("s", $searchParam);
    $disbursementStmt->execute();
    $disbursementResult = $disbursementStmt->get_result();
    
    while ($row = $disbursementResult->fetch_assoc()) {
        $results[] = $row;
    }
    
    // Search in BIR records
    $birStmt = $conn->prepare("
        SELECT 
            'BIR' as type, 
            tin as reference, 
            payee as name, 
            'BIR Tax Record' as description, 
            total_amount as amount, 
            record_date as date, 
            '' as address, 
            '' as contact_number, 
            '' as cedula_num, 
            '' as bir_type,
            tin,
            '' as sex,
            '' as civil_status,
            '' as occupation,
            '' as service_type
        FROM bir_records 
        WHERE payee LIKE ? 
        ORDER BY record_date DESC
    ");
    $birStmt->bind_param("s", $searchParam);
    $birStmt->execute();
    $birResult = $birStmt->get_result();
    
    while ($row = $birResult->fetch_assoc()) {
        $results[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Payee - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .search-box button {
            padding: 15px 30px;
            background: #1e3a5f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .search-box button:hover {
            background: #1F3A93;
            color: #ffffff;
        }

        .result-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-left: 5px solid #1F3A93;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .result-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .result-type.payment {
            background: #d4edda;
            color: #155724;
        }

        .result-type.cedula {
            background: #cce5ff;
            color: #004085;
        }

        .result-type.disbursement {
            background: #f8d7da;
            color: #721c24;
        }

        .result-type.bir {
            background: #fff3cd;
            color: #856404;
        }

        .result-amount {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a5f;
        }

        .result-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 15px;
            color: #333;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-results i {
            font-size: 80px;
            margin-bottom: 20px;
            color: #ddd;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .summary-card h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
        }

        .print-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #218838;
        }

        @media print {

            .sidebar,
            .search-box,
            .print-btn {
                display: none;
            }
        }
    </style>
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
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-search"></i> Search Payee / Taxpayer</h1>
                <p>Search for payments, receipts, and transaction history</p>
            </div>

            <div class="content-body">
                <!-- Search Box -->
                <div class="search-container">
                    <form method="GET" action="search.php">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Enter name to search..."
                                value="<?= htmlspecialchars($searchQuery) ?>"
                                required>
                            <button type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                    <p style="color: #666; font-size: 14px; margin: 0;">
                        <i class="fas fa-info-circle"></i>
                        Search by name to view all payments, cedula, BIR records, and disbursements
                    </p>
                </div>

                <?php if (!empty($searchQuery)): ?>
                <?php if (count($results) > 0): ?>

                <!-- Summary Statistics -->
                <?php
                        $totalAmount = array_sum(array_column($results, 'amount'));
                    $totalTransactions = count($results);
                    $paymentCount = count(array_filter($results, fn ($r) => $r['type'] === 'Payment'));
                    $cedulaCount = count(array_filter($results, fn ($r) => $r['type'] === 'Cedula'));
                    $birCount = count(array_filter($results, fn ($r) => $r['type'] === 'BIR'));
                    $disbursementCount = count(array_filter($results, fn ($r) => $r['type'] === 'Disbursement'));
                    ?>

                <div class="summary-stats">
                    <div class="summary-card">
                        <h4><i class="fas fa-receipt"></i> Total Transactions</h4>
                        <div class="value"><?= $totalTransactions ?>
                        </div>
                    </div>
                    <div class="summary-card">
                        <h4><i class="fas fa-peso-sign"></i> Total Amount</h4>
                        <div class="value">
                            ₱<?= number_format($totalAmount, 2) ?>
                        </div>
                    </div>
                    <div class="summary-card">
                        <h4><i class="fas fa-money-bill"></i> Payments</h4>
                        <div class="value"><?= $paymentCount ?></div>
                    </div>
                    <div class="summary-card">
                        <h4><i class="fas fa-id-card"></i> Cedula</h4>
                        <div class="value"><?= $cedulaCount ?></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Transaction History for
                        "<?= htmlspecialchars($searchQuery) ?>"</h2>
                    <button class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>

                <!-- Results -->
                <?php foreach ($results as $result): ?>
                <div class="result-card">
                    <div class="result-header">
                        <div>
                            <span
                                class="result-type <?= strtolower($result['type']) ?>">
                                <?= htmlspecialchars($result['type']) ?>
                            </span>
                            <h3 style="margin: 10px 0 5px 0; color: #1e3a5f;">
                                <?= htmlspecialchars($result['name']) ?>
                            </h3>
                            <p style="color: #666; margin: 0; font-size: 14px;">
                                <i class="fas fa-hashtag"></i>
                                <?= htmlspecialchars($result['reference']) ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <div class="result-amount">
                                ₱<?= number_format($result['amount'], 2) ?>
                            </div>
                            <button class="view-details-btn" onclick="toggleDetails(this)"
                                style="margin-top: 10px; background: #1e3a5f; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 13px;">
                                <i class="fas fa-eye"></i> View Full Details
                            </button>
                        </div>
                    </div>

                    <div class="result-details" style="display: none;">
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-calendar"></i> Date</span>
                            <span class="detail-value">
                                <?= date('F d, Y', strtotime($result['date'])) ?>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-info-circle"></i> Description</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['description']) ?>
                            </span>
                        </div>

                        <?php if (!empty($result['service_type'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-concierge-bell"></i> Service Type</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['service_type']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['address'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-map-marker-alt"></i> Address</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['address']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['contact_number'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-phone"></i> Contact</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['contact_number']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['tin'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-id-badge"></i> TIN</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['tin']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['sex'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-venus-mars"></i> Sex</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['sex']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['civil_status'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-ring"></i> Civil Status</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['civil_status']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['occupation'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-briefcase"></i> Occupation</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['occupation']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($result['cedula_num'])): ?>
                        <div class="detail-item">
                            <span class="detail-label"><i class="fas fa-id-card"></i> Cedula Number</span>
                            <span class="detail-value">
                                <?= htmlspecialchars($result['cedula_num']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h2>No Results Found</h2>
                    <p>No records found for
                        "<?= htmlspecialchars($searchQuery) ?>"</p>
                    <p>Try searching with a different name or check the spelling.</p>
                </div>
                <?php endif; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
        function toggleDetails(button) {
            const card = button.closest('.result-card');
            const details = card.querySelector('.result-details');
            const icon = button.querySelector('i');

            if (details.style.display === 'none') {
                details.style.display = 'grid';
                button.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Details';
                button.style.background = '#1F3A93';
                button.style.color = '#1e3a5f';
            } else {
                details.style.display = 'none';
                button.innerHTML = '<i class="fas fa-eye"></i> View Full Details';
                button.style.background = '#1e3a5f';
                button.style.color = 'white';
            }
        }
    </script>
</body>

</html>