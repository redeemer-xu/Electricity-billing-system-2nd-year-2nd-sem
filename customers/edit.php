<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success  = "";
$error    = "";
$customer = null;
$bills    = null;
$bill     = null;

// STEP 3: Save edited bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_bill') {
    $bill_id     = intval($_POST['bill_id']);
    $customer_id = intval($_POST['customer_id']);
    $kwh         = trim($_POST['kwh_consumed']);
    $bill_date   = trim($_POST['billing_date']);

    if (empty($kwh) || empty($bill_date)) {
        $error = "There is something wrong. Please fill in all required fields.";
        // Reload bill data
        $stmt = $conn->prepare("SELECT * FROM bills WHERE id = ?");
        $stmt->bind_param("i", $bill_id);
        $stmt->execute();
        $bill = $stmt->get_result()->fetch_assoc();
        // Reload customer
        $stmt2 = $conn->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt2->bind_param("i", $customer_id);
        $stmt2->execute();
        $customer = $stmt2->get_result()->fetch_assoc();
    } elseif (!is_numeric($kwh) || $kwh <= 0) {
        $error = "There is something wrong. kWh must be a valid positive number.";
    } else {
        $rate       = 11.00;
        $amount_due = $kwh * $rate;

        $stmt = $conn->prepare("UPDATE bills SET kwh_consumed=?, amount_due=?, billing_date=? WHERE id=?");
        $stmt->bind_param("ddsi", $kwh, $amount_due, $bill_date, $bill_id);

        if ($stmt->execute()) {
            $success = "Bill updated successfully! New amount: ₱" . number_format($amount_due, 2);
        } else {
            $error = "There is something wrong. Please try again.";
        }
    }
}

// STEP 2b: Save edited customer info
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_customer') {
    $id           = intval($_POST['id']);
    $name         = trim($_POST['name']);
    $address      = trim($_POST['address']);
    $meter_number = trim($_POST['meter_number']);
    $contact      = trim($_POST['contact']);

    if (empty($name) || empty($address) || empty($meter_number)) {
        $error = "There is something wrong. Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE customers SET name=?, address=?, meter_number=?, contact=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $address, $meter_number, $contact, $id);

        if ($stmt->execute()) {
            $success = "Customer information updated successfully!";
        } else {
            $error = "There is something wrong. Meter number may already be taken.";
        }
    }
}

// STEP 2: Load specific bill into edit form
if (isset($_GET['bill_id']) && !$bill) {
    $bill_id = intval($_GET['bill_id']);
    $stmt    = $conn->prepare("SELECT * FROM bills WHERE id = ?");
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $bill = $stmt->get_result()->fetch_assoc();

    // Also load the customer
    $stmt2 = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt2->bind_param("i", $bill['customer_id']);
    $stmt2->execute();
    $customer = $stmt2->get_result()->fetch_assoc();
}

// STEP 1b: Load customer info + all their bills
elseif (isset($_GET['customer_id']) && !$customer) {
    $cid  = intval($_GET['customer_id']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();

    $stmt2 = $conn->prepare("SELECT * FROM bills WHERE customer_id = ? ORDER BY billing_date DESC");
    $stmt2->bind_param("i", $cid);
    $stmt2->execute();
    $bills = $stmt2->get_result();
}

// STEP 0: Show customer list
$all = $conn->query("SELECT id, name, meter_number FROM customers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
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
    <!-- SUCCESS -->
    <div class="alert alert-success"><?php echo $success; ?></div>
    <a href="edit.php" class="btn btn-warning me-2">Edit Another Customer</a>
    <a href="view.php" class="btn btn-success">View All Customers</a>

  <?php elseif ($bill && $customer): ?>
    <!-- STEP 2: Edit specific bill -->
    <h5 class="mb-1">✏️ Edit Bill</h5>
    <p class="text-muted mb-4">Customer: <strong><?php echo htmlspecialchars($customer['name']); ?></strong></p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <form method="POST">
              <input type="hidden" name="action" value="save_bill">
              <input type="hidden" name="bill_id" value="<?php echo $bill['id']; ?>">
              <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">

              <div class="mb-3">
                <label class="form-label">Billing Date <span class="text-danger">*</span></label>
                <input type="date" name="billing_date" class="form-control"
                       value="<?php echo $bill['billing_date']; ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">kWh Consumed <span class="text-danger">*</span></label>
                <input type="number" name="kwh_consumed" class="form-control"
                       step="0.01" min="0.01"
                       value="<?php echo $bill['kwh_consumed']; ?>" required>
                <div class="form-text">Rate: ₱11.00 per kWh</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Current Amount Due</label>
                <input type="text" class="form-control"
                       value="₱<?php echo number_format($bill['amount_due'], 2); ?>" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">Payment Status</label>
                <input type="text" class="form-control"
                       value="<?php echo strtoupper($bill['status']); ?>" disabled>
                <div class="form-text">Change payment status from the View Customers page.</div>
              </div>
              <button type="submit" class="btn btn-warning w-100">Save Bill Changes</button>
            </form>
            <a href="edit.php?customer_id=<?php echo $customer['id']; ?>"
               class="btn btn-outline-secondary w-100 mt-2">← Back to Customer Bills</a>
          </div>
        </div>
      </div>
    </div>

  <?php elseif ($customer && $bills): ?>
    <!-- STEP 1b: Show customer info + bill list -->
    <h5 class="mb-1">✏️ Edit: <?php echo htmlspecialchars($customer['name']); ?></h5>
    <p class="text-muted mb-4">Meter No: <?php echo htmlspecialchars($customer['meter_number']); ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Customer info edit form -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-warning text-dark fw-bold">Customer Information</div>
      <div class="card-body p-4">
        <form method="POST">
          <input type="hidden" name="action" value="save_customer">
          <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control"
                     value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Meter Number <span class="text-danger">*</span></label>
              <input type="text" name="meter_number" class="form-control"
                     value="<?php echo htmlspecialchars($customer['meter_number']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Address <span class="text-danger">*</span></label>
              <input type="text" name="address" class="form-control"
                     value="<?php echo htmlspecialchars($customer['address']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact" class="form-control"
                     value="<?php echo htmlspecialchars($customer['contact']); ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-warning mt-3">Save Customer Info</button>
        </form>
      </div>
    </div>

    <!-- Bills list for this customer -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-warning text-dark fw-bold">
        Bills for this Customer
      </div>
      <div class="card-body p-0">
        <?php if ($bills->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped table-bordered mb-0 align-middle">
            <thead class="table-warning">
              <tr>
                <th>Billing Date</th>
                <th>kWh</th>
                <th>Amount Due</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($b = $bills->fetch_assoc()): ?>
              <tr>
                <td><?php echo $b['billing_date']; ?></td>
                <td><?php echo $b['kwh_consumed']; ?></td>
                <td>₱<?php echo number_format($b['amount_due'], 2); ?></td>
                <td>
                  <?php if ($b['status'] == 'paid'): ?>
                    <span class="badge bg-success">Paid</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="edit.php?bill_id=<?php echo $b['id']; ?>"
                     class="btn btn-sm btn-warning">Edit Bill</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="p-3 text-muted">No bills found for this customer.
            <a href="add_bill.php">Add a bill</a>.
          </div>
        <?php endif; ?>
      </div>
    </div>
    <a href="edit.php" class="btn btn-secondary">← Back to Customer List</a>

  <?php else: ?>
    <!-- STEP 0: Customer list -->
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
            <td>
              <a href="edit.php?customer_id=<?php echo $row['id']; ?>"
                 class="btn btn-sm btn-warning">Edit</a>
            </td>
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