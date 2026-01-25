<?php
include "../../config/database.php";
include "../../config/session.php";

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM cedula WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: list.php");
    exit;
}

$cedula = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Cedula -
        <?= htmlspecialchars($cedula['cedula_no']) ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1F3A93;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header img {
            width: 100px;
            height: 100px;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #1F3A93;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #333;
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .cedula-title {
            text-align: center;
            margin: 30px 0;
            font-size: 24px;
            color: #1F3A93;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cedula-info {
            margin: 30px 0;
        }

        .info-row {
            display: flex;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            width: 200px;
            font-weight: bold;
            color: #333;
        }

        .info-value {
            flex: 1;
            color: #666;
        }

        .amount-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
        }

        .amount-section .label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .amount-section .amount {
            font-size: 32px;
            color: #1F3A93;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #eee;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 2px solid #333;
            margin-bottom: 10px;
            height: 60px;
        }

        .signature-label {
            font-size: 12px;
            color: #666;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1F3A93;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .print-button:hover {
            background: #1e3a5f;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-container {
                box-shadow: none;
                padding: 20px;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <div class="print-container">
        <div class="header">
            <img src="../../assets/images/logo.jpg" alt="Barangay Logo">
            <h1>BARANGAY STO. ROSARIO</h1>
            <h2>Office of the Barangay Treasurer</h2>
            <p>Community Tax Certificate</p>
        </div>

        <div class="cedula-title">
            COMMUNITY TAX CERTIFICATE (CEDULA)
        </div>

        <div class="cedula-info">
            <div class="info-row">
                <div class="info-label">Cedula Number:</div>
                <div class="info-value">
                    <strong><?= htmlspecialchars($cedula['cedula_no'] ?? 'N/A') ?></strong>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Date Issued:</div>
                <div class="info-value">
                    <?= date('F d, Y', strtotime($cedula['issued_date'])) ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['full_name']) ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['address']) ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Age:</div>
                <div class="info-value">
                    <?= $cedula['age'] ?> years
                    old</div>
            </div>

            <div class="info-row">
                <div class="info-label">Occupation:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['occupation']) ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">TIN:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['tin'] ?? 'N/A') ?>
                </div>
            </div>

            <?php if (!empty($cedula['nature_of_collection'])): ?>
            <div class="info-row">
                <div class="info-label">Nature of Collection:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['nature_of_collection']) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($cedula['or_number'])): ?>
            <div class="info-row">
                <div class="info-label">OR Number:</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['or_number']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="amount-section">
            <div class="label">AMOUNT PAID</div>
            <div class="amount">
                ₱<?= number_format($cedula['amount'], 2) ?>
            </div>
        </div>

        <div class="footer">
            <p style="font-size: 12px; color: #666; margin-bottom: 20px;">
                This certifies that the community tax has been paid in accordance with the provisions
                of the Local Government Code.
            </p>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Taxpayer's Signature</div>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Barangay Treasurer</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>