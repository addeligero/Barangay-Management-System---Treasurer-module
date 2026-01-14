<?php
include "../../config/database.php";
include "../../config/session.php";

// Get last receipt number
$lastReceipt = $conn->query("SELECT receipt_no FROM payments ORDER BY id DESC LIMIT 1")->fetch_assoc();
$nextReceipt = $lastReceipt ? (intval($lastReceipt['receipt_no']) + 1) : 100001;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Payment - Barangay Sto. Rosario</title>
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
        <li><a href="list.php" class="active"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
        <li><a href="../cedula/list.php"><i class="fas fa-id-card"></i> Cedula</a></li>
        <li><a href="../bir/list.php"><i class="fas fa-percent"></i> BIR Records</a></li>
        <li><a href="../disbursement/list.php"><i class="fas fa-hand-holding-usd"></i> Disbursements</a></li>
        <li><a href="../collections/monthly.php"><i class="fas fa-chart-line"></i> Monthly Collections</a></li>
        <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-header">
        <h1><i class="fas fa-money-bill-wave"></i> Record New Payment</h1>
      </div>

      <div class="content-body">
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> Payment Information</h3>
          </div>

          <form method="POST" action="save.php">
            <div class="form-row">
              <div class="form-group">
                <label for="receipt_no"><i class="fas fa-receipt"></i> Receipt Number *</label>
                <input type="text" id="receipt_no" name="receipt_no"
                  value="<?= $nextReceipt ?>" readonly required>
              </div>

              <div class="form-group">
                <label for="payment_date"><i class="fas fa-calendar"></i> Payment Date *</label>
                <input type="date" id="payment_date" name="payment_date"
                  value="<?= date('Y-m-d') ?>"
                  required>
              </div>
            </div>

            <div class="form-group">
              <label for="payer_name"><i class="fas fa-user"></i> Payer Name *</label>
              <input type="text" id="payer_name" name="payer_name" placeholder="Enter payer's full name" required>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="service_type"><i class="fas fa-tag"></i> Service Type *</label>
                <select id="service_type" name="service_type" required>
                  <option value="">Select Service</option>
                  <option value="Barangay Clearance">Barangay Clearance</option>
                  <option value="Certificate of Indigency">Certificate of Indigency</option>
                  <option value="Certificate of Residency">Certificate of Residency</option>
                  <option value="Business Permit">Business Permit</option>
                  <option value="Community Tax Certificate">Community Tax Certificate</option>
                  <option value="Cedula">Cedula</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div class="form-group">
                <label for="purpose"><i class="fas fa-info-circle"></i> Purpose *</label>
                <input type="text" id="purpose" name="purpose" placeholder="e.g., Employment, Business, Travel"
                  required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="amount"><i class="fas fa-peso-sign"></i> Amount *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" placeholder="0.00" required>
              </div>

              <div class="form-group">
                <label for="bir_tax"><i class="fas fa-percent"></i> BIR Tax/Fee *</label>
                <input type="number" id="bir_tax" name="bir_tax" step="0.01" min="0" value="0" placeholder="0.00"
                  required>
              </div>

              <div class="form-group">
                <label for="total"><i class="fas fa-calculator"></i> Total Amount</label>
                <input type="number" id="total" name="total" step="0.01" readonly
                  style="font-weight: bold; font-size: 18px; background: #fffbea;">
              </div>
            </div>

            <div class="form-group">
              <label for="remarks"><i class="fas fa-comment"></i> Remarks</label>
              <textarea id="remarks" name="remarks" rows="3"
                placeholder="Enter any additional notes or remarks..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
              <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i> Save Payment
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
    // Calculate total automatically
    function calculateTotal() {
      const amount = parseFloat(document.getElementById('amount').value) || 0;
      const birTax = parseFloat(document.getElementById('bir_tax').value) || 0;
      const total = amount + birTax;
      document.getElementById('total').value = total.toFixed(2);
    }

    document.getElementById('amount').addEventListener('input', calculateTotal);
    document.getElementById('bir_tax').addEventListener('input', calculateTotal);
  </script>
</body>

</html>