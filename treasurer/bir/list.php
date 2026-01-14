<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM bir_records ORDER BY created_at DESC");
?>

<h3>BIR Percentage Records</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>TIN</th>
        <th>Payee</th>
        <th>Gross</th>
        <th>Base</th>
        <th>1%</th>
        <th>5%</th>
        <th>Date</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['tin'] ?></td>
        <td><?= htmlspecialchars($row['payee']) ?>
        </td>
        <td><?= number_format($row['gross_amount'], 2) ?>
        </td>
        <td><?= number_format($row['base_amount'], 2) ?>
        </td>
        <td><?= number_format($row['one_percent'], 2) ?>
        </td>
        <td><?= number_format($row['five_percent'], 2) ?>
        </td>
        <td><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>