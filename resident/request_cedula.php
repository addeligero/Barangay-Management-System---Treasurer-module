<?php
include "../config/database.php";
include "../config/resident_session.php";

function build_resident_name(array $resident, string $middleMode = 'full'): string
{
    $first = trim($resident['first_name'] ?? '');
    $middle = trim($resident['middle_name'] ?? '');
    $surname = trim($resident['surname'] ?? '');
    $suffix = trim($resident['suffix'] ?? '');

    $parts = [];
    if ($first !== '') {
        $parts[] = $first;
    }

    if ($middle !== '') {
        if ($middleMode === 'initial') {
            $parts[] = strtoupper(substr($middle, 0, 1)) . '.';
        } elseif ($middleMode === 'full') {
            $parts[] = $middle;
        }
    }

    if ($surname !== '') {
        $parts[] = $surname;
    }

    if ($suffix !== '') {
        $parts[] = $suffix;
    }

    return trim(implode(' ', $parts));
}

function build_cedula_full_name(array $resident): string
{
    $surname = trim($resident['surname'] ?? '');
    $first = trim($resident['first_name'] ?? '');
    $middle = trim($resident['middle_name'] ?? '');

    if ($surname !== '' && ($first !== '' || $middle !== '')) {
        return trim($surname . ', ' . $first . ($middle !== '' ? ' ' . $middle : ''));
    }

    return build_resident_name($resident, 'full');
}

function build_address(array $resident): string
{
    $parts = [];
    if (!empty($resident['household_no'])) {
        $parts[] = 'Household No. ' . $resident['household_no'];
    }
    if (!empty($resident['purok'])) {
        $parts[] = 'Purok ' . $resident['purok'];
    }
    if (!empty($resident['barangay'])) {
        $parts[] = $resident['barangay'];
    }
    if (!empty($resident['municipality'])) {
        $parts[] = $resident['municipality'];
    }
    if (!empty($resident['province'])) {
        $parts[] = $resident['province'];
    }

    return implode(', ', $parts);
}

function normalize_civil_status(?string $status): string
{
    $status = trim((string) $status);
    if ($status === 'Divorced') {
        return 'Separated';
    }

    return $status;
}

function normalize_citizenship(?string $nationality): string
{
    $nationality = trim((string) $nationality);
    if ($nationality === 'Filipino' || $nationality === '') {
        return 'Filipino';
    }

    if ($nationality === 'Dual Citizen' || $nationality === 'Foreign National') {
        return $nationality;
    }

    return 'Foreign National';
}

function compute_age_from_birthdate(?string $birthDate): int
{
    if (empty($birthDate)) {
        return 0;
    }

    $birth = new DateTime($birthDate);
    $today = new DateTime('today');

    return (int) $birth->diff($today)->y;
}

$residentId = intval($_SESSION['resident_id']);
$stmt = $conn->prepare("SELECT id, first_name, middle_name, surname, suffix, username, barangay, municipality, province, purok, household_no, birthdate, birthplace, age, sex, civil_status, nationality, occupation, occupation_type, annual_income, account_status FROM residents WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $residentId);
$stmt->execute();
$result = $stmt->get_result();
$resident = $result->fetch_assoc();
$stmt->close();

if (!$resident) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=session");
    exit;
}

$status = strtolower(trim($resident['account_status'] ?? 'active'));
if ($status !== 'active') {
    session_unset();
    session_destroy();
    header("Location: login.php?error=account");
    exit;
}

// Get last cedula number
$lastCedula = $conn->query("SELECT cedula_no FROM cedula ORDER BY id DESC LIMIT 1")->fetch_assoc();
$nextCedula = $lastCedula && isset($lastCedula['cedula_no']) ? (intval($lastCedula['cedula_no']) + 1) : 2025001;

$fullNameDefault = build_cedula_full_name($resident);
$addressDefault = build_address($resident);
$birthDateDefault = !empty($resident['birthdate']) ? date('Y-m-d', strtotime($resident['birthdate'])) : '';
$ageDefault = !empty($resident['age']) ? intval($resident['age']) : compute_age_from_birthdate($birthDateDefault);
$civilStatusDefault = normalize_civil_status($resident['civil_status'] ?? '');
$citizenshipDefault = normalize_citizenship($resident['nationality'] ?? '');
$occupationDefault = trim($resident['occupation'] ?? '') !== '' ? $resident['occupation'] : ($resident['occupation_type'] ?? '');
$placeOfIssueDefault = trim(($resident['municipality'] ?? '') . (empty($resident['province']) ? '' : ', ' . $resident['province']));
$annualIncomeDefault = isset($resident['annual_income']) && floatval($resident['annual_income']) > 0
    ? number_format((float) $resident['annual_income'], 2, '.', '')
    : '';

$success = '';
$error = '';

if (isset($_GET['submitted'])) {
    $success = 'Cedula request submitted. Please wait for treasurer approval.';
} elseif (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedulaNo = trim($_POST['cedula_no'] ?? '');
    $orNumber = trim($_POST['or_number'] ?? '');
    $issuedDate = trim($_POST['issued_date'] ?? '');
    $yearIssued = isset($_POST['year_issued']) && $_POST['year_issued'] !== ''
        ? intval($_POST['year_issued'])
        : (empty($issuedDate) ? intval(date('Y')) : intval(date('Y', strtotime($issuedDate))));
    $placeOfIssue = trim($_POST['place_of_issue'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $age = compute_age_from_birthdate($birthDate);
    $sex = trim($_POST['sex'] ?? '');
    $birthPlace = trim($_POST['birth_place'] ?? '');
    $civilStatus = trim($_POST['civil_status'] ?? '');
    $citizenship = trim($_POST['citizenship'] ?? '');
    $icrNo = trim($_POST['icr_no'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $tin = trim($_POST['tin'] ?? '');
    $height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : 0;
    $weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? floatval($_POST['weight']) : 0;
    $annualIncome = isset($_POST['annual_income']) ? floatval($_POST['annual_income']) : 0;
    $basicTax = isset($_POST['basic_tax']) ? floatval($_POST['basic_tax']) : 5.00;
    $additionalBusiness = isset($_POST['additional_tax_business']) ? floatval($_POST['additional_tax_business']) : 0;
    $additionalProfession = isset($_POST['additional_tax_profession']) ? floatval($_POST['additional_tax_profession']) : 0;
    $additionalProperty = isset($_POST['additional_tax_property']) ? floatval($_POST['additional_tax_property']) : 0;
    $communityTaxDue = $basicTax + $additionalBusiness + $additionalProfession + $additionalProperty;
    $interest = isset($_POST['interest']) ? floatval($_POST['interest']) : 0;
    $amount = $communityTaxDue + $interest;
    $natureOfCollection = trim($_POST['nature_of_collection'] ?? '');
    $amountInWords = trim($_POST['amount_in_words'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($cedulaNo === '' || $issuedDate === '' || $placeOfIssue === '' || $fullName === '' || $surname === '' || $firstName === '' || $address === '' || $birthDate === '' || $sex === '' || $birthPlace === '' || $civilStatus === '' || $citizenship === '' || $occupation === '' || $natureOfCollection === '') {
        $error = 'Please fill in all required fields.';
    } else {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO cedula
            (cedula_no, or_number, issued_date, year_issued, place_of_issue, full_name, surname, first_name, middle_name, address, birth_date, age, sex, birth_place, civil_status, citizenship, icr_no, occupation, tin, height, weight, annual_income, basic_tax, additional_tax_business, additional_tax_profession, additional_tax_property, community_tax_due, interest, amount, nature_of_collection, amount_in_words, remarks, issued_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $issuedBy = null;
        $stmt->bind_param(
            "sssisssssssisssssssddddddddddsssi",
            $cedulaNo,
            $orNumber,
            $issuedDate,
            $yearIssued,
            $placeOfIssue,
            $fullName,
            $surname,
            $firstName,
            $middleName,
            $address,
            $birthDate,
            $age,
            $sex,
            $birthPlace,
            $civilStatus,
            $citizenship,
            $icrNo,
            $occupation,
            $tin,
            $height,
            $weight,
            $annualIncome,
            $basicTax,
            $additionalBusiness,
            $additionalProfession,
            $additionalProperty,
            $communityTaxDue,
            $interest,
            $amount,
            $natureOfCollection,
            $amountInWords,
            $remarks,
            $issuedBy
        );

        $cedulaOk = $stmt->execute();
        $stmt->close();

        $paymentOk = false;
        if ($cedulaOk) {
            $certificateType = 'Cedula';
            $purpose = 'Cedula Request';
            $residentName = $fullName;
            $birTax = 0;

            $payStmt = $conn->prepare("
                INSERT INTO payment_status (resident_id, certificate_type, purpose, resident_fname, payment_status, amount, bir_tax, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())
            ");
            $payStmt->bind_param("isssdd", $residentId, $certificateType, $purpose, $residentName, $amount, $birTax);
            $paymentOk = $payStmt->execute();
            $payStmt->close();
        }

        if ($cedulaOk && $paymentOk) {
            $conn->commit();
            header('Location: request_cedula.php?submitted=1');
            exit;
        }

        $conn->rollback();
        $error = 'Failed to submit cedula request. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Cedula - Resident Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="resident-portal">
    <div class="mobile-topbar">
        <button class="mobile-menu-btn" type="button" id="mobileMenuBtn" aria-label="Open menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="portal-title">Resident Portal</div>
    </div>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.jpg" alt="Barangay Logo"
                    style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; border: 3px solid #ffffff;">
                <h2>BARANGAY STO. ROSARIO</h2>
                <p>Resident Portal</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="pending_payments.php"><i class="fas fa-hourglass-half"></i> Pending Payments</a></li>
                <li><a href="payment_history.php"><i class="fas fa-receipt"></i> Payment History</a></li>
                <li><a href="request_cedula.php" class="active"><i class="fas fa-id-card"></i> Request Cedula</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-id-card"></i> Request Cedula</h1>
                <p>Welcome, <?= htmlspecialchars($fullNameDefault) ?>
                </p>
            </div>

            <div class="content-body">
                <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-pen"></i> Cedula Request Form</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Please review your details and fill
                            any missing information.</p>
                    </div>

                    <form method="POST" action="request_cedula.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cedula_no"><i class="fas fa-hashtag"></i> Cedula Number *</label>
                                <input type="text" id="cedula_no" name="cedula_no"
                                    value="<?= htmlspecialchars((string) $nextCedula) ?>"
                                    readonly required>
                            </div>

                            <div class="form-group">
                                <label for="or_number"><i class="fas fa-receipt"></i> OR Number</label>
                                <input type="text" id="or_number" name="or_number"
                                    placeholder="To be assigned by treasurer">
                            </div>

                            <div class="form-group">
                                <label for="issued_date"><i class="fas fa-calendar"></i> Date Issued *</label>
                                <input type="date" id="issued_date" name="issued_date"
                                    value="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="year_issued"><i class="fas fa-calendar-check"></i> Year *</label>
                                <input type="number" id="year_issued" name="year_issued"
                                    value="<?= date('Y') ?>"
                                    required>
                            </div>

                            <div class="form-group" style="flex: 2;">
                                <label for="place_of_issue"><i class="fas fa-map"></i> Place of Issue (City/Mun/Prov)
                                    *</label>
                                <input type="text" id="place_of_issue" name="place_of_issue"
                                    value="<?= htmlspecialchars($placeOfIssueDefault) ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
                            <input type="text" id="full_name" name="full_name"
                                value="<?= htmlspecialchars($fullNameDefault) ?>"
                                required autocomplete="off">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="surname"><i class="fas fa-user"></i> Surname *</label>
                                <input type="text" id="surname" name="surname"
                                    value="<?= htmlspecialchars($resident['surname'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="first_name"><i class="fas fa-user"></i> First Name *</label>
                                <input type="text" id="first_name" name="first_name"
                                    value="<?= htmlspecialchars($resident['first_name'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="middle_name"><i class="fas fa-user"></i> Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name"
                                    value="<?= htmlspecialchars($resident['middle_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address"><i class="fas fa-map-marker-alt"></i> Complete Address *</label>
                            <textarea id="address" name="address" rows="2"
                                required><?= htmlspecialchars($addressDefault) ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_date"><i class="fas fa-birthday-cake"></i> Birth Date *</label>
                                <input type="date" id="birth_date" name="birth_date"
                                    value="<?= htmlspecialchars($birthDateDefault) ?>"
                                    required onchange="calculateAge()">
                            </div>

                            <div class="form-group">
                                <label for="age"><i class="fas fa-sort-numeric-up"></i> Age *</label>
                                <input type="number" id="age" name="age"
                                    value="<?= htmlspecialchars((string) $ageDefault) ?>"
                                    readonly required>
                            </div>

                            <div class="form-group">
                                <label for="sex"><i class="fas fa-venus-mars"></i> Sex *</label>
                                <select id="sex" name="sex" required>
                                    <option value="">Select</option>
                                    <option value="Male" <?= ($resident['sex'] ?? '') === 'Male' ? 'selected' : '' ?>>Male
                                    </option>
                                    <option value="Female" <?= ($resident['sex'] ?? '') === 'Female' ? 'selected' : '' ?>>Female
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_place"><i class="fas fa-hospital"></i> Place of Birth *</label>
                                <input type="text" id="birth_place" name="birth_place"
                                    value="<?= htmlspecialchars($resident['birthplace'] ?? '') ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="civil_status"><i class="fas fa-ring"></i> Civil Status *</label>
                                <select id="civil_status" name="civil_status" required>
                                    <option value="">Select</option>
                                    <option value="Single" <?= $civilStatusDefault === 'Single' ? 'selected' : '' ?>>Single
                                    </option>
                                    <option value="Married" <?= $civilStatusDefault === 'Married' ? 'selected' : '' ?>>Married
                                    </option>
                                    <option value="Widowed" <?= $civilStatusDefault === 'Widowed' ? 'selected' : '' ?>>Widowed
                                    </option>
                                    <option value="Separated" <?= $civilStatusDefault === 'Separated' ? 'selected' : '' ?>>Separated
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="citizenship"><i class="fas fa-flag"></i> Citizenship *</label>
                                <select id="citizenship" name="citizenship" required>
                                    <option value="Filipino" <?= $citizenshipDefault === 'Filipino' ? 'selected' : '' ?>>Filipino
                                    </option>
                                    <option value="Dual Citizen" <?= $citizenshipDefault === 'Dual Citizen' ? 'selected' : '' ?>>Dual
                                        Citizen</option>
                                    <option value="Foreign National" <?= $citizenshipDefault === 'Foreign National' ? 'selected' : '' ?>>Foreign
                                        National</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="icr_no"><i class="fas fa-id-badge"></i> ICR No. (If Alien)</label>
                                <input type="text" id="icr_no" name="icr_no" placeholder="ICR no.">
                            </div>

                            <div class="form-group">
                                <label for="occupation"><i class="fas fa-briefcase"></i> Occupation *</label>
                                <input type="text" id="occupation" name="occupation"
                                    value="<?= htmlspecialchars($occupationDefault) ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="tin"><i class="fas fa-id-card-alt"></i> TIN (Optional)</label>
                                <input type="text" id="tin" name="tin" placeholder="000-000-000-000">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="height"><i class="fas fa-arrows-alt-v"></i> Height (cm)</label>
                                <input type="number" id="height" name="height" step="0.01" placeholder="e.g., 165">
                            </div>

                            <div class="form-group">
                                <label for="weight"><i class="fas fa-weight"></i> Weight (kg)</label>
                                <input type="number" id="weight" name="weight" step="0.01" placeholder="e.g., 65">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nature_of_collection"><i class="fas fa-list"></i> Nature of Collection
                                    *</label>
                                <input type="text" id="nature_of_collection" name="nature_of_collection"
                                    value="Community Tax" required>
                            </div>

                            <div class="form-group">
                                <label for="annual_income"><i class="fas fa-coins"></i> Taxable Amount (PHP)</label>
                                <input type="number" id="annual_income" name="annual_income" step="0.01" min="0"
                                    value="<?= htmlspecialchars($annualIncomeDefault) ?>"
                                    oninput="calculateAdditionalFromIncome()">
                                <small style="color:#666;">Auto-calculated for profession: Income &divide; 1,000</small>
                            </div>

                            <div class="form-group">
                                <label for="basic_tax"><i class="fas fa-receipt"></i> Basic Community Tax (PHP)</label>
                                <input type="number" id="basic_tax" name="basic_tax" step="0.01" min="0" value="5.00"
                                    oninput="computeTotals()">
                                <small style="color:#666;">Regular: 5.00, Voluntary/Exempted: 1.00</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="additional_tax_business"><i class="fas fa-store"></i> Additional Tax -
                                    Business (PHP)</label>
                                <input type="number" id="additional_tax_business" name="additional_tax_business"
                                    step="0.01" min="0" value="0.00" oninput="computeTotals()">
                            </div>

                            <div class="form-group">
                                <label for="additional_tax_profession"><i class="fas fa-briefcase"></i> Additional Tax -
                                    Profession (PHP)</label>
                                <input type="number" id="additional_tax_profession" name="additional_tax_profession"
                                    step="0.01" min="0" value="0.00" oninput="computeTotals()">
                            </div>

                            <div class="form-group">
                                <label for="additional_tax_property"><i class="fas fa-home"></i> Additional Tax -
                                    Property (PHP)</label>
                                <input type="number" id="additional_tax_property" name="additional_tax_property"
                                    step="0.01" min="0" value="0.00" oninput="computeTotals()">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="community_tax_due"><i class="fas fa-calculator"></i> Community Tax Due
                                    (PHP)</label>
                                <input type="number" id="community_tax_due" name="community_tax_due" step="0.01"
                                    value="5.00" readonly required
                                    style="background:#e8f0ff; font-weight:bold; font-size:16px;">
                            </div>

                            <div class="form-group">
                                <label for="interest"><i class="fas fa-percentage"></i> Interest (PHP)</label>
                                <input type="number" id="interest" name="interest" step="0.01" min="0" value="0.00"
                                    oninput="computeTotals()">
                            </div>

                            <div class="form-group">
                                <label for="amount"><i class="fas fa-peso-sign"></i> Total Amount Paid *</label>
                                <input type="number" id="amount" name="amount" step="0.01" value="5.00" required
                                    readonly style="background:#e8f0ff; font-weight:bold; font-size:16px;">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label for="amount_in_words"><i class="fas fa-spell-check"></i> Amount in Words</label>
                                <input type="text" id="amount_in_words" name="amount_in_words"
                                    placeholder="e.g., Seventy nine pesos">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="remarks"><i class="fas fa-comment"></i> Remarks</label>
                            <textarea id="remarks" name="remarks" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-paper-plane"></i> Submit Cedula Request
                            </button>
                            <a href="pending_payments.php" class="btn btn-secondary"
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
        function computeTotals() {
            const basicTax = parseFloat(document.getElementById('basic_tax').value) || 0;
            const additionalBusiness = parseFloat(document.getElementById('additional_tax_business').value) || 0;
            const additionalProfession = parseFloat(document.getElementById('additional_tax_profession').value) || 0;
            const additionalProperty = parseFloat(document.getElementById('additional_tax_property').value) || 0;
            const interest = parseFloat(document.getElementById('interest').value) || 0;
            const due = basicTax + additionalBusiness + additionalProfession + additionalProperty;
            document.getElementById('community_tax_due').value = due.toFixed(2);
            document.getElementById('amount').value = (due + interest).toFixed(2);
        }

        function calculateAdditionalFromIncome() {
            const income = parseFloat(document.getElementById('annual_income').value) || 0;
            const computed = income / 1000;
            const professionField = document.getElementById('additional_tax_profession');
            if (professionField) {
                professionField.value = (computed > 0 ? computed : 0).toFixed(2);
            }
            computeTotals();
        }

        function calculateAge() {
            const birthDateValue = document.getElementById('birth_date').value;
            if (!birthDateValue) {
                return;
            }
            const birthDate = new Date(birthDateValue);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.getElementById('age').value = age;
        }

        function syncFullName() {
            const surname = document.getElementById('surname').value.trim();
            const first = document.getElementById('first_name').value.trim();
            const middle = document.getElementById('middle_name').value.trim();
            if (!surname && !first && !middle) {
                return;
            }
            let fullName = '';
            if (surname && (first || middle)) {
                fullName = surname + ', ' + first + (middle ? ' ' + middle : '');
            } else {
                fullName = surname || first || middle;
            }
            document.getElementById('full_name').value = fullName;
        }

        ['surname', 'first_name', 'middle_name'].forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (!field) {
                return;
            }
            field.addEventListener('input', syncFullName);
        });

        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.querySelector('.sidebar');

        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        document.addEventListener('DOMContentLoaded', computeTotals);
    </script>
</body>

</html>