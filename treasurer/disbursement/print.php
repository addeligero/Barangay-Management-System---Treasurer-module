<?php
include "../../config/database.php";
include "../../config/session.php";

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM disbursements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: list.php");
    exit;
}

$disbursement = $result->fetch_assoc();

function parseAccountingEntries($text)
{
    $rows = [];
    $text = trim((string) $text);
    if ($text === '') {
        return $rows;
    }

    if ($text[0] === '[' || $text[0] === '{') {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
                $decoded = [$decoded];
            }
            foreach ($decoded as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $rows[] = [
                    'name' => $row['name'] ?? '',
                    'code' => $row['code'] ?? '',
                    'debit' => $row['debit'] ?? '',
                    'credit' => $row['credit'] ?? ''
                ];
            }
            if (!empty($rows)) {
                return $rows;
            }
        }
    }

    $lines = preg_split('/\R/', $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        $rows[] = [
            'name' => $parts[0] ?? '',
            'code' => $parts[1] ?? '',
            'debit' => $parts[2] ?? '',
            'credit' => $parts[3] ?? ''
        ];
    }
    return $rows;
}

$accountingRows = parseAccountingEntries($disbursement['accounting_entries'] ?? '');
$amount = number_format((float) $disbursement['amount'], 2);
$releaseAmount = number_format((float) $disbursement['release_amount'], 2);
$disburseDate = date('M d, Y', strtotime($disbursement['disburse_date']));
$receivedDate = !empty($disbursement['received_date'])
    ? date('M d, Y', strtotime($disbursement['received_date']))
    : $disburseDate;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Disbursement Voucher -
        <?= htmlspecialchars($disbursement['dv_no']) ?>
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #111;
        }

        .print-toolbar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .print-btn,
        .back-btn {
            border: none;
            background: #1f3a93;
            color: #fff;
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .back-btn {
            background: #2c5282;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .voucher {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #222;
        }

        .voucher-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-bottom: 2px solid #222;
        }

        .voucher-header .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .voucher-header .center {
            flex: 1;
            text-align: center;
        }

        .voucher-header .center .line-1 {
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .voucher-header .center .line-2 {
            font-size: 12px;
        }

        .voucher-header .center .line-3 {
            font-size: 12px;
        }

        .voucher-header .center .line-4 {
            font-size: 11px;
            margin-top: 2px;
        }

        .voucher-title {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            padding: 6px 0 8px;
            border-bottom: 2px solid #222;
            letter-spacing: 1px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            border-bottom: 2px solid #222;
        }

        .meta-left,
        .meta-right {
            padding: 10px 12px;
        }

        .meta-row {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .meta-row .label {
            width: 70px;
            font-weight: bold;
        }

        .meta-right .label {
            width: 80px;
        }

        .particulars-table {
            width: 100%;
            border-collapse: collapse;
        }

        .particulars-table th,
        .particulars-table td {
            border: 1px solid #222;
            padding: 8px;
            font-size: 12px;
            vertical-align: top;
        }

        .particulars-table th {
            background: #f1f1f1;
            text-align: center;
            font-weight: bold;
        }

        .particulars-amount {
            text-align: right;
            font-weight: bold;
        }

        .cert-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 2px solid #222;
            border-bottom: 2px solid #222;
        }

        .cert-box {
            border-right: 1px solid #222;
            padding: 8px 10px;
            min-height: 110px;
            font-size: 11px;
        }

        .cert-box:last-child {
            border-right: none;
        }

        .cert-box .sign-name {
            margin-top: 28px;
            font-weight: bold;
            text-align: center;
        }

        .cert-box .sign-label {
            text-align: center;
            font-size: 10px;
        }

        .received-section {
            border-bottom: 2px solid #222;
            padding: 8px 10px;
            font-size: 11px;
        }

        .received-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 16px;
            margin-top: 10px;
        }

        .received-sign .sign-line {
            margin-top: 24px;
            border-top: 1px solid #111;
            text-align: center;
            padding-top: 4px;
            font-weight: bold;
        }

        .received-sign .sign-label {
            text-align: center;
            font-size: 10px;
        }

        .received-fields .field-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #111;
            padding: 6px 0 4px;
            gap: 10px;
        }

        .received-fields .field-row:last-child {
            border-bottom: none;
        }

        .accounting-section {
            padding: 8px 10px;
            border-bottom: 2px solid #222;
        }

        .accounting-title {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .accounting-table {
            width: 100%;
            border-collapse: collapse;
        }

        .accounting-table th,
        .accounting-table td {
            border: 1px solid #222;
            padding: 6px;
            font-size: 11px;
        }

        .accounting-table th {
            background: #f1f1f1;
            text-align: center;
        }

        .accounting-table td.amount {
            text-align: right;
        }

        .footer-sign {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 10px 12px 16px;
        }

        .footer-box {
            text-align: center;
            font-size: 11px;
        }

        .footer-box .name {
            margin-top: 24px;
            border-top: 1px solid #111;
            padding-top: 4px;
            font-weight: bold;
        }

        .footer-box .label {
            font-size: 10px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .print-toolbar {
                display: none;
            }

            .voucher {
                border: none;
                max-width: none;
            }
        }
    </style>
</head>

<body>
    <div class="print-toolbar">
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <a class="back-btn" href="list.php"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="voucher">
        <div class="voucher-header">
            <img class="logo" src="../../assets/images/logo.jpg" alt="Barangay Logo">
            <div class="center">
                <div class="line-1">REPUBLIC OF THE PHILIPPINES</div>
                <div class="line-2">PROVINCE OF AGUSAN DEL NORTE</div>
                <div class="line-3">MUNICIPALITY OF MAGALLANES</div>
                <div class="line-4">BARANGAY STO. ROSARIO / TIN NO: 004-75-387-000</div>
            </div>
            <img class="logo" src="../../assets/images/logo.jpg" alt="Barangay Logo">
        </div>

        <div class="voucher-title">DISBURSEMENT VOUCHER</div>

        <div class="meta-grid">
            <div class="meta-left">
                <div class="meta-row">
                    <div class="label">Payee:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['payee']) ?>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="label">Address:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['payee_address'] ?? '') ?>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="label">TIN No.:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['payee_tin'] ?? '') ?>
                    </div>
                </div>
            </div>
            <div class="meta-right">
                <div class="meta-row">
                    <div class="label">DV No.:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['dv_no']) ?>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="label">Date:</div>
                    <div><?= $disburseDate ?></div>
                </div>
                <div class="meta-row">
                    <div class="label">Fund:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['fund'] ?? '') ?>
                    </div>
                </div>
                <div class="meta-row">
                    <div class="label">Check No.:</div>
                    <div>
                        <?= htmlspecialchars($disbursement['check_no']) ?>
                    </div>
                </div>
            </div>
        </div>

        <table class="particulars-table">
            <thead>
                <tr>
                    <th>PARTICULARS</th>
                    <th style="width: 180px;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= nl2br(htmlspecialchars($disbursement['purpose'])) ?>
                    </td>
                    <td class="particulars-amount">Php.
                        <?= $amount ?></td>
                </tr>
            </tbody>
        </table>

        <div class="cert-section">
            <div class="cert-box">
                <div><strong>A.</strong> Certified as to availability of appropriation for obligation.</div>
                <div class="sign-name">
                    <?= htmlspecialchars($disbursement['signatory_a'] ?? '') ?>
                </div>
                <div class="sign-label">Chairman, Committee on Appropriation</div>
            </div>
            <div class="cert-box">
                <div><strong>B.</strong> Certified as to availability of funds for the purpose and completeness &amp;
                    propriety of supporting documents.</div>
                <div class="sign-name">
                    <?= htmlspecialchars($disbursement['signatory_b'] ?? '') ?>
                </div>
                <div class="sign-label">Barangay Treasurer</div>
            </div>
            <div class="cert-box">
                <div><strong>C.</strong> Certified as to validity, propriety and legality of claim &amp; approved for
                    payment.</div>
                <div class="sign-name">
                    <?= htmlspecialchars($disbursement['signatory_c'] ?? '') ?>
                </div>
                <div class="sign-label">Punong Barangay</div>
            </div>
        </div>

        <div class="received-section">
            <strong>D. RECEIVED PAYMENT</strong>
            <div class="received-grid">
                <div class="received-sign">
                    <div class="sign-line">
                        <?= htmlspecialchars($disbursement['signatory_received_by'] ?? $disbursement['payee']) ?>
                    </div>
                    <div class="sign-label">Signature over printed name</div>
                </div>
                <div class="received-fields">
                    <div class="field-row">
                        <span>Check No.</span>
                        <span><?= htmlspecialchars($disbursement['check_no']) ?></span>
                    </div>
                    <div class="field-row">
                        <span>OR No.</span>
                        <span><?= htmlspecialchars($disbursement['or_no'] ?? '') ?></span>
                    </div>
                    <div class="field-row">
                        <span>Date</span>
                        <span><?= $receivedDate ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="accounting-section">
            <div class="accounting-title">E. ACCOUNTING ENTRIES</div>
            <table class="accounting-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">ACCOUNT NAME</th>
                        <th style="width: 20%;">ACCOUNT CODE</th>
                        <th style="width: 15%;">DEBIT</th>
                        <th style="width: 15%;">CREDIT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($accountingRows)): ?>
                    <?php foreach ($accountingRows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['code']) ?>
                        </td>
                        <td class="amount">
                            <?= htmlspecialchars($row['debit']) ?>
                        </td>
                        <td class="amount">
                            <?= htmlspecialchars($row['credit']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td class="amount"></td>
                        <td class="amount"></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-sign">
            <div class="footer-box">
                <div>Prepared by:</div>
                <div class="name">
                    <?= htmlspecialchars($disbursement['signatory_prepared_by'] ?? '') ?>
                </div>
                <div class="label">Barangay Treasurer</div>
            </div>
            <div class="footer-box">
                <div>Checked by:</div>
                <div class="name">
                    <?= htmlspecialchars($disbursement['signatory_checked_by'] ?? '') ?>
                </div>
                <div class="label">Barangay Bookkeeper</div>
            </div>
            <div class="footer-box">
                <div>Approved by:</div>
                <div class="name">
                    <?= htmlspecialchars($disbursement['signatory_approved_by'] ?? '') ?>
                </div>
                <div class="label">Municipal Accountant</div>
            </div>
        </div>
    </div>
</body>

</html>