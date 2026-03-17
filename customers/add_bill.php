<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success = "";
$error   = "";

// Fetch all customers for the dropdown
$customers = $conn->query("SELECT id, name, meter_number FROM customers ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $kwh         = trim($_POST['kwh_consumed']);
    $bill_date   = trim($_POST['billing_date']);

    if (empty($customer_id) || empty($kwh) || empty($bill_date)) {
        $error = "There is something wrong. Please fill in all required fields.";
    } elseif (!is_numeric($kwh) || $kwh <= 0) {
        $error = "There is something wrong. kWh must be a valid positive number.";
    } else {
        // Check if this customer already has a bill for that month
        $check = $conn->prepare("SELECT id FROM bills WHERE customer_id = ? AND billing_date = ?");
        $check->bind_param("is", $customer_id, $bill_date);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "There is something wrong. This customer already has a bill for that date.";
        } else {
            $rate       = 11.00;
            $amount_due = $kwh * $rate;

            $stmt = $conn->prepare("INSERT INTO bills (customer_id, kwh_consumed, amount_due, billing_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idds", $customer_id, $kwh, $amount_due, $bill_date);

            if ($stmt->execute()) {
                // Get customer name for success message
                $c = $conn->prepare("SELECT name FROM customers WHERE id = ?");
                $c->bind_param("i", $customer_id);
                $c->execute();
                $cname = $c->get_result()->fetch_assoc()['name'];

                $success = "Bill added for <strong>" . htmlspecialchars($cname) . "</strong>! 
                            Amount due: <strong>₱" . number_format($amount_due, 2) . "</strong>";
            } else {
                $error = "There is something wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Bill — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
  <div class="container">
    <span class="navbar-brand">⚡ Electricity Billing System</span>
    <a href="../dashboard.php" class="text-white">← Back to Dashboard</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h5 class="mb-4">⚡ Add New Bill</h5>

          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="add_bill.php" class="btn btn-primary w-100 mb-2">Add Another Bill</a>
            <a href="view.php" class="btn btn-success w-100 mb-2">View All Customers</a>
            <a href="../dashboard.php" class="btn btn-secondary w-100">Return to Dashboard</a>
          <?php else: ?>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($customers->num_rows > 0): ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select" required>
                  <option value="">— Choose a customer —</option>
                  <?php
                  // Reset pointer in case it was used above
                  $customers->data_seek(0);
                  while ($c = $customers->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>">
                      <?php echo htmlspecialchars($c['name']); ?> 
                      (Meter: <?php echo htmlspecialchars($c['meter_number']); ?>)
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Billing Date <span class="text-danger">*</span></label>
                <input type="date" name="billing_date" class="form-control"
                       value="<?php echo date('Y-m-d'); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">kWh Consumed <span class="text-danger">*</span></label>
                <input type="number" name="kwh_consumed" class="form-control"
                       step="0.01" min="0.01" required>
                <div class="form-text">Rate: ₱11.00 per kWh</div>
              </div>

              <!-- Live bill preview -->
              <div class="alert alert-info d-none" id="preview">
                Estimated bill: <strong id="preview-amount">₱0.00</strong>
              </div>

              <button type="submit" class="btn btn-primary w-100">Save Bill</button>
            </form>
            <?php else: ?>
              <div class="alert alert-warning">No customers registered yet. 
                <a href="add.php">Register a customer first</a>.
              </div>
            <?php endif; ?>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Live bill calculator
document.querySelector('input[name="kwh_consumed"]')?.addEventListener('input', function() {
    const kwh    = parseFloat(this.value) || 0;
    const rate   = 11.00;
    const amount = kwh * rate;
    const preview = document.getElementById('preview');
    const amountEl = document.getElementById('preview-amount');
    if (kwh > 0) {
        amountEl.textContent = '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2});
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});
</script>
</body>
</html>