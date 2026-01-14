<?php
include "../config/database.php";
include "../config/session.php";

$totalCollection = $conn->query("
  SELECT SUM(amount) AS total FROM payments
")->fetch_assoc()['total'];

$totalDisbursement = $conn->query("
  SELECT SUM(release_amount) AS total FROM disbursements
")->fetch_assoc()['total'];
?>

<h2>Treasurer Dashboard</h2>

<p>Total Collection: ₱<?= number_format($totalCollection, 2) ?></p>
<p>Total Disbursement: ₱<?= number_format($totalDisbursement, 2) ?>
</p>

<canvas id="chart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('chart'), {
        type: 'bar',
        data: {
            labels: ['Collection', 'Disbursement'],
            datasets: [{
                data: [ <?= $totalCollection ?> , <?= $totalDisbursement ?> ]
            }]
        }
    });
</script>