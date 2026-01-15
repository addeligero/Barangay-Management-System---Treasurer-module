<?php
include "../../config/database.php";
include "../../config/session.php";

// Get last cedula number
$lastCedula = $conn->query("SELECT cedula_no FROM cedula ORDER BY id DESC LIMIT 1")->fetch_assoc();
$nextCedula = $lastCedula && isset($lastCedula['cedula_no']) ? (intval($lastCedula['cedula_no']) + 1) : 2025001;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Cedula - Barangay Sto. Rosario</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <li><a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../payments/list.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-id-card"></i> Cedula</a></li>
                <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
                <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
                <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-id-card"></i> Issue New Cedula</h1>
            </div>

            <div class="content-body">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> Cedula Information</h3>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">Community Tax Certificate - Complete
                            all required information</p>
                    </div>

                    <form method="POST" action="save.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cedula_no"><i class="fas fa-hashtag"></i> Cedula Number *</label>
                                <input type="text" id="cedula_no" name="cedula_no"
                                    value="<?= $nextCedula ?>"
                                    readonly required>
                            </div>

                            <div class="form-group">
                                <label for="issued_date"><i class="fas fa-calendar"></i> Date Issued *</label>
                                <input type="date" id="issued_date" name="issued_date"
                                    value="<?= date('Y-m-d') ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group" style="position: relative;">
                            <label for="full_name"><i class="fas fa-user"></i> Full Name * <small
                                    style="color: #666;">(Type to search existing records)</small></label>
                            <input type="text" id="full_name" name="full_name"
                                placeholder="Enter full name (First M. Last)" required autocomplete="off">
                            <div id="suggestions"
                                style="position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; width: calc(100% - 40px); z-index: 1000; display: none; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 5px;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address"><i class="fas fa-map-marker-alt"></i> Complete Address *</label>
                            <textarea id="address" name="address" rows="2"
                                placeholder="Street, Barangay, Municipality, Province" required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_date"><i class="fas fa-birthday-cake"></i> Birth Date *</label>
                                <input type="date" id="birth_date" name="birth_date" required onchange="calculateAge()">
                            </div>

                            <div class="form-group">
                                <label for="age"><i class="fas fa-sort-numeric-up"></i> Age *</label>
                                <input type="number" id="age" name="age" readonly required>
                            </div>

                            <div class="form-group">
                                <label for="sex"><i class="fas fa-venus-mars"></i> Sex *</label>
                                <select id="sex" name="sex" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_place"><i class="fas fa-hospital"></i> Place of Birth *</label>
                                <input type="text" id="birth_place" name="birth_place" placeholder="City/Municipality"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="civil_status"><i class="fas fa-ring"></i> Civil Status *</label>
                                <select id="civil_status" name="civil_status" required>
                                    <option value="">Select</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="occupation"><i class="fas fa-briefcase"></i> Occupation *</label>
                                <input type="text" id="occupation" name="occupation" placeholder="Enter occupation"
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

                            <div class="form-group">
                                <label for="amount"><i class="fas fa-peso-sign"></i> Amount *</label>
                                <input type="number" id="amount" name="amount" step="0.01" value="50.00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="remarks"><i class="fas fa-comment"></i> Remarks</label>
                            <textarea id="remarks" name="remarks" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-save"></i> Issue Cedula
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
        function calculateAge() {
            const birthDate = new Date(document.getElementById('birth_date').value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            document.getElementById('age').value = age;
        }

        // Autocomplete for full name with auto-fill
        const nameInput = document.getElementById('full_name');
        const suggestionsDiv = document.getElementById('suggestions');
        let debounceTimer;

        nameInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const searchTerm = this.value.trim();

            if (searchTerm.length < 2) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`../payments/get_people.php?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            suggestionsDiv.innerHTML = data.map(person =>
                                `<div class="suggestion-item" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;" data-name="${person.name}">
                                    <i class="fas fa-user"></i> ${person.name}
                                    <small style="color: #666; margin-left: 10px;">(${person.source === 'cedula' ? 'Has cedula record' : 'Payment record'})</small>
                                </div>`
                            ).join('');
                            suggestionsDiv.style.display = 'block';

                            // Add click handlers
                            document.querySelectorAll('.suggestion-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    const name = this.dataset.name;
                                    nameInput.value = name;
                                    suggestionsDiv.style.display = 'none';

                                    // Fetch person details to auto-fill
                                    fetch(
                                            `get_person.php?name=${encodeURIComponent(name)}`)
                                        .then(response => response.json())
                                        .then(personData => {
                                            if (!personData.error) {
                                                // Auto-fill fields if they exist in the record
                                                if (personData.address) document
                                                    .getElementById('address')
                                                    .value = personData.address;
                                                if (personData.birth_date) {
                                                    document.getElementById(
                                                            'birth_date')
                                                        .value = personData
                                                        .birth_date;
                                                    calculateAge();
                                                }
                                                if (personData.sex) document
                                                    .getElementById('sex')
                                                    .value = personData.sex;
                                                if (personData.birth_place)
                                                    document.getElementById(
                                                        'birth_place').value =
                                                    personData.birth_place;
                                                if (personData.civil_status)
                                                    document.getElementById(
                                                        'civil_status').value =
                                                    personData.civil_status;
                                                if (personData.occupation)
                                                    document.getElementById(
                                                        'occupation').value =
                                                    personData.occupation;
                                                if (personData.tin) document
                                                    .getElementById('tin')
                                                    .value = personData.tin;
                                                if (personData.height) document
                                                    .getElementById('height')
                                                    .value = personData.height;
                                                if (personData.weight) document
                                                    .getElementById('weight')
                                                    .value = personData.weight;

                                                // Show notification
                                                alert(
                                                    '✓ Information auto-filled from previous record!');
                                            }
                                        });
                                });

                                item.addEventListener('mouseenter', function() {
                                    this.style.background = '#f0f4f8';
                                });

                                item.addEventListener('mouseleave', function() {
                                    this.style.background = 'white';
                                });
                            });
                        } else {
                            suggestionsDiv.style.display = 'none';
                        }
                    });
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== nameInput && e.target !== suggestionsDiv) {
                suggestionsDiv.style.display = 'none';
            }
        });
    </script>
</body>

</html>