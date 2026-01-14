<form method="POST" action="save.php">
  Payer Name: <input name="payer_name"><br>
  Service: <select name="service_type">
    <option>Barangay Clearance</option>
    <option>Cedula</option>
  </select><br>
  Purpose: <input name="purpose"><br>
  Amount: <input name="amount" type="number" step="0.01"><br>
  BIR Tax: <input name="bir_tax" type="number" step="0.01"><br>
  Receipt No: <input name="receipt_no"><br>
  <button>Save</button>
</form>
