<?php
require_once "../includes/session_check.php";
require_once "../includes/db_connect.php";

$success  = "";
$confirm  = false;
$customer = null;

// Step 3: Confirmed — delete the customer
if (isset($_POST['confirm_delete'])) {
    $id   = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Customer deleted successfully!";
    }
}

// Step 2: Show confirmation screen
elseif (isset($_GET['id'])) {
    $id   = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $confirm  = true;
}

// Step 1: Show customer list
$all = $conn->query("SELECT id, name, meter_number FROM customers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Delete Customer — Electricity Billing</title>
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
    <!-- Step 4: Success -->
    <div class="alert alert-success"><?php echo $success; ?></div>
    <a href="../dashboard.php" class="btn btn-primary">Return to Dashboard</a>

  <?php elseif ($confirm && $customer): ?>
    <!-- Step 2: Confirm Delete -->
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card border-danger shadow-sm">
          <div class="card-body p-4 text-center">
            <div class="fs-1">⚠️</div>
            <h5 class="mt-2">Confirm Delete</h5>
            <p class="text-muted">Are you sure you want to delete:</p>
            <h6 class="text-danger"><?php echo htmlspecialchars($customer['name']); ?></h6>
            <p class="small text-muted">Meter No: <?php echo htmlspecialchars($customer['meter_number']); ?></p>
            <p class="small text-danger">This will also delete all their billing records!</p>
            <div class="d-flex gap-2 justify-content-center mt-3">
              <!-- YES — delete -->
              <form method="POST">
                <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
              </form>
              <!-- NO — go back -->
              <a href="delete.php" class="btn btn-secondary">No, Cancel</a>
            </div>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- Step 1: Customer List -->
    <h5 class="mb-4">🗑️ Select a Customer to Delete</h5>
    <?php if ($all->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-danger">
          <tr><th>#</th><th>Name</th><th>Meter No.</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php $count = 1; while ($row = $all->fetch_assoc()): ?>
          <tr>
            <td><?php echo $count++; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['meter_number']); ?></td>
            <td><a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Delete</a></td>
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