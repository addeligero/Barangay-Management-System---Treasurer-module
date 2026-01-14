<?php
include "../../config/database.php";
$result = $conn->query("SELECT * FROM payments");
?>

<table border="1">
    <tr>
        <th>Payer</th>
        <th>Service</th>
        <th>Amount</th>
        <th>Date</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['payer_name'] ?></td>
        <td><?= $row['service_type'] ?></td>
        <td><?= number_format($row['amount'], 2) ?>
        </td>
        <td><?= $row['paid_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>