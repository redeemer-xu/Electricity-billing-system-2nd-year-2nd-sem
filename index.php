<?php
// (Optional) show errors while setting up; remove later if you like
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/includes/header.php';
?>
<div class="text-center">
  <h1 class="mb-3">Welcome to Electricity Billing System</h1>
  <p class="text-muted">Start from the dashboard to follow your flowchart.</p>

  <!-- Corrected Link Tag -->
  <a href="<?= app_url('dashboard.php'); ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
