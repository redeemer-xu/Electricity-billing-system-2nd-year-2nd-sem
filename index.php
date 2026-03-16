<?php
session_start();

// If already logged in, skip the login page
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = ""; // stores error message if login fails

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get what the user typed
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Connect to database
    require_once "includes/db_connect.php";

    // Look up the username in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if password matches the hashed one in DB
        if (password_verify($password, $user['password'])) {
            // Login success — save user to session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "Username not found. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Electricity Billing</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body p-4">

          <h4 class="text-center mb-1">⚡ Electricity Billing</h4>
          <p class="text-center text-muted mb-4">Sign in to continue</p>

          <!-- Show error message if login failed -->
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>