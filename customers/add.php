<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name         = trim($_POST['name']);
    $address      = trim($_POST['address']);
    $meter_number = trim($_POST['meter_number']);
    $contact      = trim($_POST['contact']);
    $kwh          = trim($_POST['kwh_consumed']);

    // Validate inputs
    if (empty($name) || empty($address) || empty($meter_number) || empty($kwh)) {
        $error = "There is something wrong. Please fill in all required fields.";
    } elseif (!is_numeric($kwh) || $kwh <= 0) {
        $error = "There is something wrong. kWh must be a valid positive number.";
    } else {
        // Calculate bill — ₱11.00 per kWh (Philippine rate)
        $rate       = 11.00;
        $amount_due = $kwh * $rate;
        $bill_date  = date('Y-m-d');

        // Insert into customers table
        $stmt = $conn->prepare("INSERT INTO customers (name, address, meter_number, contact) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $address, $meter_number, $contact);

        if ($stmt->execute()) {
            $customer_id = $conn->insert_id; // get the new customer's ID

            // Insert into bills table
            $stmt2 = $conn->prepare("INSERT INTO bills (customer_id, kwh_consumed, amount_due, billing_date) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("idds", $customer_id, $kwh, $amount_due, $bill_date);
            $stmt2->execute();

            $success = "Customer added successfully! Bill of ₱" . number_format($amount_due, 2) . " has been calculated.";
        } else {
            $error = "There is something wrong. Meter number may already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Customer — Electricity Billing</title>
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
          <h5 class="mb-4">➕ Add New Customer</h5>

          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="../dashboard.php" class="btn btn-primary w-100">Return to Dashboard</a>
          <?php else: ?>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Meter Number <span class="text-danger">*</span></label>
                <input type="text" name="meter_number" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">kWh Consumed <span class="text-danger">*</span></label>
                <input type="number" name="kwh_consumed" class="form-control" step="0.01" min="0" required>
                <div class="form-text">Rate: ₱11.00 per kWh</div>
              </div>
              <button type="submit" class="btn btn-primary w-100">Add Customer & Calculate Bill</button>
            </form>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>