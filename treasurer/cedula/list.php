<?php
include "../../config/database.php";
include "../../config/session.php";

$result = $conn->query("SELECT * FROM cedula ORDER BY issued_date DESC");
?>

<h3>Cedula Records</h3>

<table border="1" cellpadding="5">
    <tr>
        <th>Full Name</th>
        <th>Address</th>
        <th>Age</th>
        <th>Occupation</th>
        <th>Amount</th>
        <th>Date Issued</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['full_name']) ?>
        </td>
        <td><?= htmlspecialchars($row['address']) ?>
        </td>
        <td><?= $row['age'] ?></td>
        <td><?= htmlspecialchars($row['occupation']) ?>
        </td>
        <td><?= number_format($row['amount'], 2) ?>
        </td>
        <td><?= $row['issued_date'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>