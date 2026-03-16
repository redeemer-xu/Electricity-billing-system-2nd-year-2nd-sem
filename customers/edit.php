<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success = "";
$error   = "";
$customer = null;

// Step 2: Save the edited data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id           = intval($_POST['id']);
    $name         = trim($_POST['name']);
    $address      = trim($_POST['address']);
    $meter_number = trim($_POST['meter_number']);
    $contact      = trim($_POST['contact']);
    $kwh          = trim($_POST['kwh_consumed']);

    if (empty($name) || empty($address) || empty($meter_number) || empty($kwh)) {
        $error = "There is something wrong. Please fill in all required fields.";
        // Reload customer data to keep form filled
        $stmt = $conn->prepare("SELECT c.*, b.kwh_consumed FROM customers c LEFT JOIN bills b ON c.id = b.customer_id WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
    } elseif (!is_numeric($kwh) || $kwh <= 0) {
        $error = "There is something wrong. kWh must be a valid positive number.";
        $stmt = $conn->prepare("SELECT c.*, b.kwh_consumed FROM customers c LEFT JOIN bills b ON c.id = b.customer_id WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
    } else {
        $rate       = 11.00;
        $amount_due = $kwh * $rate;

        // Update customers table
        $stmt = $conn->prepare("UPDATE customers SET name=?, address=?, meter_number=?, contact=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $address, $meter_number, $contact, $id);

        // Update bills table
        $stmt2 = $conn->prepare("UPDATE bills SET kwh_consumed=?, amount_due=? WHERE customer_id=?");
        $stmt2->bind_param("ddi", $kwh, $amount_due, $id);

        if ($stmt->execute() && $stmt2->execute()) {
            $success = "Customer updated successfully!";
        } else {
            $error = "There is something wrong. Please try again.";
        }
    }
}

// Step 1: Load customer data into form when Edit is clicked
if (isset($_GET['id']) && !$customer) {
    $id   = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT c.*, b.kwh_consumed FROM customers c LEFT JOIN bills b ON c.id = b.customer_id WHERE c.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
}

// Step 0: Show list of customers
$all = $conn->query("SELECT id, name, meter_number FROM customers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Customer — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
  <div class="container">
    <span class="navbar-brand">⚡ Electricity Billing System</span>
    <a href="../dashboard.php" class="text-white">← Back to Dashboard</a>
  </div>
</nav>

<div class="container mt-4">

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <a href="../dashboard.php" class="btn btn-primary">Return to Dashboard</a>

  <?php elseif ($customer): ?>
    <!-- Edit Form -->
    <h5 class="mb-4">✏️ Edit Customer</h5>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <form method="POST">
              <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
              <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Meter Number <span class="text-danger">*</span></label>
                <input type="text" name="meter_number" class="form-control" value="<?php echo htmlspecialchars($customer['meter_number']); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($customer['contact']); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">kWh Consumed <span class="text-danger">*</span></label>
                <input type="number" name="kwh_consumed" class="form-control" step="0.01" min="0" value="<?php echo $customer['kwh_consumed']; ?>" required>
                <div class="form-text">Rate: ₱11.00 per kWh</div>
              </div>
              <button type="submit" class="btn btn-warning w-100">Save Changes</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- Customer List -->
    <h5 class="mb-4">✏️ Select a Customer to Edit</h5>
    <?php if ($all->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-warning">
          <tr><th>#</th><th>Name</th><th>Meter No.</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php $count = 1; while ($row = $all->fetch_assoc()): ?>
          <tr>
            <td><?php echo $count++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['meter_number']); ?></td>
            <td><a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="alert alert-info">No customers found. <a href="add.php">Add one first</a>.</div>
    <?php endif; ?>
    <a href="../dashboard.php" class="btn btn-secondary mt-3">← Return to Dashboard</a>
  <?php endif; ?>

</div>
</body>
</html>