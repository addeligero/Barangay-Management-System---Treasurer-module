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
$issuedDate = !empty($cedula['issued_date']) ? date('m/d/Y', strtotime($cedula['issued_date'])) : '';
$birthDate = !empty($cedula['birth_date']) ? date('m/d/Y', strtotime($cedula['birth_date'])) : '';
$yearIssued = !empty($cedula['year_issued'])
    ? $cedula['year_issued']
    : intval(date('Y', strtotime($cedula['issued_date'])));
$placeOfIssue = $cedula['place_of_issue'] ?? '';
$surnameDisplay = trim($cedula['surname'] ?? '');
$firstDisplay = trim($cedula['first_name'] ?? '');
$middleDisplay = trim($cedula['middle_name'] ?? '');
$fullNameDisplay = trim($cedula['full_name'] ?? '');
if ($surnameDisplay === '' && $firstDisplay === '' && $middleDisplay === '') {
    $surnameDisplay = $fullNameDisplay;
}
$basicTax = number_format((float) ($cedula['basic_tax'] ?? 5), 2);
$additionalBusiness = number_format((float) ($cedula['additional_tax_business'] ?? 0), 2);
$additionalProfession = number_format((float) ($cedula['additional_tax_profession'] ?? 0), 2);
$additionalProperty = number_format((float) ($cedula['additional_tax_property'] ?? 0), 2);
$communityTaxDue = number_format((float) ($cedula['community_tax_due'] ?? 0), 2);
$interest = number_format((float) ($cedula['interest'] ?? 0), 2);
$amountPaid = number_format((float) ($cedula['amount'] ?? 0), 2);
$amountInWords = $cedula['amount_in_words'] ?? '';
$annualIncome = isset($cedula['annual_income']) ? number_format((float) $cedula['annual_income'], 2) : '';
$height = isset($cedula['height']) ? htmlspecialchars((string) $cedula['height']) : '';
$weight = isset($cedula['weight']) ? htmlspecialchars((string) $cedula['weight']) : '';
$isMale = ($cedula['sex'] ?? '') === 'Male';
$isFemale = ($cedula['sex'] ?? '') === 'Female';
$civilStatus = $cedula['civil_status'] ?? '';
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

        .cedula-sheet {
            max-width: 980px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #1F3A93;
        }

        .header-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr;
            border-bottom: 2px solid #1F3A93;
            text-align: center;
            font-weight: bold;
        }

        .header-row div {
            padding: 10px 8px;
            border-right: 1px solid #1F3A93;
        }

        .header-row div:last-child {
            border-right: none;
        }

        .header-title {
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .header-sub {
            font-size: 16px;
        }

        .header-no {
            font-size: 18px;
            color: #1F3A93;
        }

        .auditor-copy {
            font-size: 10px;
            color: #444;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-row {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            border-bottom: 1px solid #1F3A93;
        }

        .meta-block {
            padding: 8px;
            border-right: 1px solid #1F3A93;
        }

        .meta-block:last-child {
            border-right: none;
        }

        .meta-label {
            font-size: 11px;
            color: #444;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 14px;
            font-weight: bold;
            min-height: 18px;
        }

        .name-row {
            border-bottom: 1px solid #1F3A93;
            padding: 8px;
        }

        .name-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-top: 6px;
        }

        .name-cell {
            border: 1px solid #1F3A93;
            padding: 6px;
            min-height: 42px;
        }

        .name-label {
            font-size: 11px;
            color: #444;
        }

        .name-value {
            font-size: 14px;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-bottom: 1px solid #1F3A93;
        }

        .info-item {
            border-right: 1px solid #1F3A93;
            border-bottom: 1px solid #1F3A93;
            padding: 6px 8px;
            min-height: 52px;
        }

        .info-item:nth-child(3n) {
            border-right: none;
        }

        .info-label {
            font-size: 11px;
            color: #444;
        }

        .info-value {
            font-size: 13px;
            font-weight: bold;
            min-height: 16px;
        }

        .check-group {
            display: flex;
            gap: 10px;
            font-size: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .check-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            text-align: center;
            line-height: 12px;
            margin-right: 4px;
            font-weight: bold;
        }

        .tax-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tax-table th,
        .tax-table td,
        .summary-table td {
            border: 1px solid #1F3A93;
            padding: 6px 8px;
            font-size: 12px;
            vertical-align: top;
        }

        .tax-table th {
            text-align: left;
            background: #f4f7ff;
        }

        .amount-cell {
            width: 140px;
            text-align: right;
            font-weight: bold;
        }

        .summary-wrap {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .thumb-box {
            border: 1px solid #1F3A93;
            min-height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #444;
        }

        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 18px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
        }

        .signature-label {
            font-size: 11px;
            text-align: center;
            margin-top: 6px;
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

            .cedula-sheet {
                box-shadow: none;
                padding: 12px;
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

    <div class="cedula-sheet">
        <div class="header-row">
            <div class="header-title">COMMUNITY TAX CERTIFICATE</div>
            <div class="header-sub">INDIVIDUAL</div>
            <div class="header-no">
                <?= htmlspecialchars($cedula['cedula_no'] ?? '') ?>
                <div class="auditor-copy">Auditor's Copy</div>
            </div>
        </div>

        <div class="meta-row">
            <div class="meta-block">
                <div class="meta-label">Year</div>
                <div class="meta-value">
                    <?= htmlspecialchars((string) $yearIssued) ?>
                </div>
            </div>
            <div class="meta-block">
                <div class="meta-label">Place of Issue (City/Mun/Prov)</div>
                <div class="meta-value">
                    <?= htmlspecialchars($placeOfIssue) ?></div>
            </div>
            <div class="meta-block">
                <div class="meta-label">Date Issued</div>
                <div class="meta-value">
                    <?= htmlspecialchars($issuedDate) ?></div>
            </div>
        </div>

        <div class="name-row">
            <div class="meta-label">Name</div>
            <div class="name-grid">
                <div class="name-cell">
                    <div class="name-label">(Surname)</div>
                    <div class="name-value">
                        <?= htmlspecialchars($surnameDisplay) ?>
                    </div>
                </div>
                <div class="name-cell">
                    <div class="name-label">(First)</div>
                    <div class="name-value">
                        <?= htmlspecialchars($firstDisplay) ?></div>
                </div>
                <div class="name-cell">
                    <div class="name-label">(Middle)</div>
                    <div class="name-value">
                        <?= htmlspecialchars($middleDisplay) ?></div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Address</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['address'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Citizenship</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['citizenship'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">ICR No. (If Alien)</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['icr_no'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Civil Status</div>
                <div class="info-value">
                    <div class="check-group">
                        <span><span
                                class="check-box"><?= $civilStatus === 'Single' ? 'X' : '' ?></span>Single</span>
                        <span><span
                                class="check-box"><?= $civilStatus === 'Married' ? 'X' : '' ?></span>Married</span>
                        <span><span
                                class="check-box"><?= $civilStatus === 'Widowed' ? 'X' : '' ?></span>Widowed</span>
                        <span><span
                                class="check-box"><?= $civilStatus === 'Separated' ? 'X' : '' ?></span>Separated</span>
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Sex</div>
                <div class="info-value">
                    <div class="check-group">
                        <span><span
                                class="check-box"><?= $isMale ? 'X' : '' ?></span>Male</span>
                        <span><span
                                class="check-box"><?= $isFemale ? 'X' : '' ?></span>Female</span>
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Date of Birth</div>
                <div class="info-value">
                    <?= htmlspecialchars($birthDate) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Place of Birth</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['birth_place'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Height</div>
                <div class="info-value"><?= $height ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Weight</div>
                <div class="info-value"><?= $weight ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Profession / Occupation / Business</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['occupation'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">TIN (If Any)</div>
                <div class="info-value">
                    <?= htmlspecialchars($cedula['tin'] ?? '') ?>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Community Taxable Amount</div>
                <div class="info-value">
                    <?= htmlspecialchars($annualIncome) ?></div>
            </div>
        </div>

        <table class="tax-table">
            <thead>
                <tr>
                    <th>Community Tax</th>
                    <th class="amount-cell">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>A. Basic Community Tax (P5.00) Voluntary or Exempted (P1.00)</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($basicTax) ?></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>B. Additional Community Tax (P1.00 for every P1,000)</strong></td>
                </tr>
                <tr>
                    <td>1. Gross receipts or earnings derived from business during the preceding year</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($additionalBusiness) ?>
                    </td>
                </tr>
                <tr>
                    <td>2. Salaries or gross receipts or earnings derived from exercise of profession or pursuit of any
                        occupation</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($additionalProfession) ?>
                    </td>
                </tr>
                <tr>
                    <td>3. Income from real property</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($additionalProperty) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="summary-wrap">
            <div>
                <div class="thumb-box">Right Thumb Print</div>
                <div class="signature-row">
                    <div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Taxpayer's Signature</div>
                    </div>
                    <div>
                        <div class="signature-line"></div>
                        <div class="signature-label">Municipal / City Treasurer</div>
                    </div>
                </div>
            </div>
            <table class="summary-table">
                <tr>
                    <td>Community Tax Due</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($communityTaxDue) ?>
                    </td>
                </tr>
                <tr>
                    <td>Interest</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($interest) ?></td>
                </tr>
                <tr>
                    <td>Total Amount Paid</td>
                    <td class="amount-cell">
                        <?= htmlspecialchars($amountPaid) ?></td>
                </tr>
                <tr>
                    <td colspan="2">In words:
                        <?= htmlspecialchars($amountInWords) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>