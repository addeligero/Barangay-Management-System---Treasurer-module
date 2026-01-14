<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM disbursements ORDER BY disburse_date DESC");
?>

<h3>Disbursement Records</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>Date</th>
        <th>Check #</th>
        <th>Payee</th>
        <th>DV #</th>
        <th>Amount</th>
        <th>Fund</th>
        <th>Purpose</th>
        <th>Released</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['disburse_date'] ?></td>
        <td><?= $row['check_no'] ?></td>
        <td><?= htmlspecialchars($row['payee']) ?>
        </td>
        <td><?= $row['dv_no'] ?></td>
        <td><?= number_format($row['amount'], 2) ?>
        </td>
        <td><?= $row['fund'] ?></td>
        <td><?= htmlspecialchars($row['purpose']) ?>
        </td>
        <td><?= number_format($row['release_amount'], 2) ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>