<?php
require_once "includes/session_check.php";
require_once "includes/db_connect.php";

$success = "";
$error   = "";

// Get current rate
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'rate_per_kwh'");
$current_rate = $result->fetch_assoc()['setting_value'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_rate = trim($_POST['rate_per_kwh']);

    if (empty($new_rate)) {
        $error = "There is something wrong. Rate cannot be empty.";
    } elseif (!is_numeric($new_rate) || $new_rate <= 0) {
        $error = "There is something wrong. Rate must be a valid positive number.";
    } else {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'rate_per_kwh'");
        $stmt->bind_param("s", $new_rate);

        if ($stmt->execute()) {
            $current_rate = $new_rate;
            $success = "Rate updated to ₱" . number_format($new_rate, 2) . " per kWh successfully!";
        } else {
            $error = "There is something wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
  <div class="container">
    <span class="navbar-brand">⚡ Electricity Billing System</span>
    <a href="dashboard.php" class="text-white">← Back to Dashboard</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold">
          ⚙️ System Settings
        </div>
        <div class="card-body p-4">

          <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Rate per kWh (₱) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" name="rate_per_kwh" class="form-control"
                       step="0.01" min="0.01"
                       value="<?php echo htmlspecialchars($current_rate); ?>" required>
                <span class="input-group-text">per kWh</span>
              </div>
              <div class="form-text">Current rate: <strong>₱<?php echo number_format($current_rate, 2); ?> per kWh</strong></div>
            </div>
            <button type="submit" class="btn btn-secondary w-100">Save Rate</button>
          </form>

        </div>
      </div>

      <div class="card shadow-sm mt-3">
        <div class="card-header bg-light fw-bold">ℹ️ How rates work</div>
        <div class="card-body">
          <ul class="small text-muted mb-0">
            <li>The rate set here is used for all <strong>new bills</strong> added via Add Bill page</li>
            <li>Existing bills already saved in the database are <strong>not affected</strong></li>
            <li>To update an existing bill's amount, use the <strong>Edit</strong> page</li>
          </ul>
        </div>
      </div>

      <a href="dashboard.php" class="btn btn-secondary w-100 mt-3">← Return to Dashboard</a>
    </div>
  </div>
</div>
</body>
</html>