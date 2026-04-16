<?php
include "../../config/database.php";
include "../../config/session.php";

$paymentId = intval($_GET['id'] ?? 0);
if ($paymentId <= 0) {
    header("Location: list.php?error=Invalid payment ID.");
    exit;
}

$stmt = $conn->prepare("SELECT payments.*, users.name AS received_by_name FROM payments LEFT JOIN users ON payments.received_by = users.id WHERE payments.id = ?");
$stmt->bind_param("i", $paymentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: list.php?error=Payment not found.");
    exit;
}

$payment = $result->fetch_assoc();
$stmt->close();

$paymentDate = !empty($payment['payment_date'])
    ? date('F d, Y', strtotime($payment['payment_date']))
    : date('F d, Y');
$amount = (float) ($payment['amount'] ?? 0);
$birTax = (float) ($payment['bir_tax'] ?? 0);
$total = $amount + $birTax;
$remarks = trim((string) ($payment['remarks'] ?? ''));
$receivedBy = trim((string) ($payment['received_by_name'] ?? ''));
$printedOn = date('F d, Y h:i A');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Payment Record</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: #f5f6f8;
            color: #222;
        }

        .page {
            max-width: 860px;
            margin: 30px auto;
            background: #fff;
            padding: 32px 40px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .print-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 24px;
        }

        .print-actions button,
        .print-actions a {
            background: #1f3c88;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .print-actions a {
            background: #5a5f6a;
        }

        .receipt-header {
            display: flex;
            gap: 16px;
            align-items: center;
            border-bottom: 2px solid #e4e7ec;
            padding-bottom: 18px;
            margin-bottom: 26px;
        }

        .receipt-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #1f3c88;
            object-fit: cover;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-header p {
            margin: 6px 0 8px;
            font-size: 14px;
            color: #555;
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 18px;
            color: #1f3c88;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 13px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        th,
        td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #e8eaef;
            vertical-align: top;
        }

        th {
            width: 30%;
            background: #f7f8fb;
            font-weight: 600;
            color: #333;
        }

        .total-row td {
            font-weight: 700;
            color: #1f3c88;
            background: #f0f4ff;
        }

        .signature-block {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }

        .signature {
            flex: 1;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 6px;
            font-size: 12px;
            color: #555;
            text-align: center;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="print-actions">
            <button type="button" onclick="window.print()">Print</button>
            <a href="list.php">Back to Payments</a>
        </div>

        <div class="receipt-header">
            <img src="../../assets/images/logo.jpg" alt="Barangay Logo">
            <div>
                <h1>Barangay Sto. Rosario</h1>
                <p>Magallanes, Agusan del Norte</p>
                <h2>Payment Record</h2>
            </div>
        </div>

        <div class="meta-row">
            <div>Receipt No:
                <strong><?= htmlspecialchars($payment['receipt_no']) ?></strong>
            </div>
            <div>Printed: <?= htmlspecialchars($printedOn) ?></div>
        </div>

        <table>
            <tr>
                <th>Payment Date</th>
                <td><?= htmlspecialchars($paymentDate) ?></td>
            </tr>
            <tr>
                <th>Payer Name</th>
                <td><?= htmlspecialchars($payment['payer_name']) ?>
                </td>
            </tr>
            <tr>
                <th>Service Type</th>
                <td><?= htmlspecialchars($payment['service_type']) ?>
                </td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td><?= htmlspecialchars($payment['purpose']) ?>
                </td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>PHP <?= number_format($amount, 2) ?></td>
            </tr>
            <tr>
                <th>BIR Tax/Fee</th>
                <td>PHP <?= number_format($birTax, 2) ?></td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td>PHP <?= number_format($total, 2) ?></td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td><?= $remarks !== '' ? htmlspecialchars($remarks) : 'N/A' ?>
                </td>
            </tr>
            <tr>
                <th>Received By</th>
                <td><?= $receivedBy !== '' ? htmlspecialchars($receivedBy) : 'N/A' ?>
                </td>
            </tr>
        </table>

        <div class="signature-block">
            <div class="signature">
                <div class="signature-line">Payer Signature</div>
            </div>
            <div class="signature">
                <div class="signature-line">Treasurer Signature</div>
            </div>
        </div>
    </div>
</body>

</html>