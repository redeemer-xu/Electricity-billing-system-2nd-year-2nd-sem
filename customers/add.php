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

    if (empty($name) || empty($address) || empty($meter_number)) {
        $error = "There is something wrong. Please fill in all required fields.";
    } else {
        // Check if meter number already exists
        $check = $conn->prepare("SELECT id FROM customers WHERE meter_number = ?");
        $check->bind_param("s", $meter_number);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "There is something wrong. That meter number is already registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO customers (name, address, meter_number, contact) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $address, $meter_number, $contact);

            if ($stmt->execute()) {
                $success = "Customer registered successfully!";
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
          <h5 class="mb-4">➕ Register New Customer</h5>

          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <a href="add_bill.php" class="btn btn-primary w-100 mb-2">Add Bill for This Customer</a>
            <a href="../dashboard.php" class="btn btn-secondary w-100">Return to Dashboard</a>
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
              <button type="submit" class="btn btn-primary w-100">Register Customer</button>
            </form>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>