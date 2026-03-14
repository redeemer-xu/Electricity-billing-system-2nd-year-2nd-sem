<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/includes/header.php';
?>
<h2 class="mb-4">Dashboard</h2>

<div class="row g-3">
  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('add_customer.php'); ?>" class="btn btn-primary w-100">Add Customer</a>
  </div>

  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('view_customers.php'); ?>" class="btn btn-primary w-100">View Customers</a>
  </div>

  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('add_consumption.php'); ?>" class="btn btn-primary w-100">Add Consumption</a>
  </div>

  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('generate_receipt.php'); ?>" class="btn btn-primary w-100">Generate Receipt</a>
  </div>

  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('update_customer.php'); ?>" class="btn btn-primary w-100">Update Customer</a>
  </div>

  <div class="col-12 col-md-6 col-lg-4">
    <a href="<?= app_url('delete_customer.php'); ?>" class="btn btn-danger w-100">Delete Customer</a>
  </div>

  <div class="col-12">
    <a href="<?= app_url('logout.php'); ?>" class="btn btn-secondary w-100">Exit</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
