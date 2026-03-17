<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success = "";

// Handle Mark as Paid / Unpaid toggle
if (isset($_GET['toggle_status'])) {
    $bill_id    = intval($_GET['toggle_status']);
    $new_status = $_GET['status'] === 'unpaid' ? 'paid' : 'unpaid';

    $stmt = $conn->prepare("UPDATE bills SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $bill_id);
    if ($stmt->execute()) {
        $success = "Payment status updated to " . strtoupper($new_status) . "!";
    }
}

// Fetch all customers with their bill info
$result = $conn->query("
    SELECT c.id, c.name, c.address, c.meter_number, c.contact,
           b.id AS bill_id, b.kwh_consumed, b.amount_due, b.billing_date, b.status
    FROM customers c
    LEFT JOIN bills b ON c.id = b.customer_id
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>View Customers — Electricity Billing</title>
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
  <h5 class="mb-4">📋 All Customers</h5>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Address</th>
          <th>Meter No.</th>
          <th>Contact</th>
          <th>kWh</th>
          <th>Amount Due</th>
          <th>Billing Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $count++; ?></td>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['address']); ?></td>
          <td><?php echo htmlspecialchars($row['meter_number']); ?></td>
          <td><?php echo htmlspecialchars($row['contact']); ?></td>
          <td><?php echo $row['kwh_consumed']; ?></td>
          <td>₱<?php echo number_format($row['amount_due'], 2); ?></td>
          <td><?php echo $row['billing_date']; ?></td>
          <td>
            <?php if ($row['status'] == 'paid'): ?>
              <span class="badge bg-success">Paid</span>
            <?php else: ?>
              <span class="badge bg-danger">Unpaid</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($row['bill_id']): ?>
              <?php if ($row['status'] == 'unpaid'): ?>
                <a href="view.php?toggle_status=<?php echo $row['bill_id']; ?>&status=unpaid"
                   class="btn btn-sm btn-success"
                   onclick="return confirm('Mark this bill as PAID?')">
                   ✓ Mark Paid
                </a>
              <?php else: ?>
                <a href="view.php?toggle_status=<?php echo $row['bill_id']; ?>&status=paid"
                   class="btn btn-sm btn-warning"
                   onclick="return confirm('Mark this bill as UNPAID?')">
                   ↩ Mark Unpaid
                </a>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="alert alert-info">No customers found. <a href="add.php">Add one now</a>.</div>
  <?php endif; ?>

  <a href="../dashboard.php" class="btn btn-secondary mt-3">← Return to Dashboard</a>
</div>
</body>
</html>