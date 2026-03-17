<?php
require_once "includes/session_check.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-dark bg-primary">
  <div class="container">
    <span class="navbar-brand">⚡ Electricity Billing System</span>
    <span class="text-white">
      Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
      &nbsp;|&nbsp;
      <a href="logout.php" class="text-white">Logout</a>
    </span>
  </div>
</nav>

<!-- Dashboard Cards -->
<div class="container mt-5">
  <h5 class="mb-4">What would you like to do?</h5>
  <div class="row g-3">

    <div class="col-md-3 col-sm-6">
      <a href="customers/add.php" class="text-decoration-none">
        <div class="card text-center p-4 h-100 border-primary">
          <div class="fs-1">➕</div>
          <h6 class="mt-2">Add Customer</h6>
          <p class="text-muted small">Register a new customer</p>
        </div>
      </a>
    </div>

    <div class="col-md-3 col-sm-6">
      <a href="customers/view.php" class="text-decoration-none">
        <div class="card text-center p-4 h-100 border-success">
          <div class="fs-1">📋</div>
          <h6 class="mt-2">View Customers</h6>
          <p class="text-muted small">Show all customer records</p>
        </div>
      </a>
    </div>

    <div class="col-md-3 col-sm-6">
      <a href="customers/edit.php" class="text-decoration-none">
        <div class="card text-center p-4 h-100 border-warning">
          <div class="fs-1">✏️</div>
          <h6 class="mt-2">Update Customer</h6>
          <p class="text-muted small">Edit customer details</p>
        </div>
      </a>
    </div>

    <!-- DELETE CARD — this was the missing one -->
    <div class="col-md-3 col-sm-6">
      <a href="customers/delete.php" class="text-decoration-none">
        <div class="card text-center p-4 h-100 border-danger">
          <div class="fs-1">🗑️</div>
          <h6 class="mt-2">Delete Customer</h6>
          <p class="text-muted small">Remove a customer record</p>
        </div>
      </a>
    </div>
    
    <div class="col-md-3 col-sm-6">
      <a href="customers/add_bill.php" class="text-decoration-none">
        <div class="card text-center p-4 h-100 border-info">
          <div class="fs-1">⚡</div>
          <h6 class="mt-2">Add Bill</h6>
          <p class="text-muted small">Add monthly bill for existing customer</p>
        </div>
      </a>
    </div>
</div>

  </div>
</div>

</body>
</html>