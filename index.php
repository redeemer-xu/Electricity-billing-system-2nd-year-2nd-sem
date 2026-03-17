<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $conn = new mysqli("127.0.0.1", "root", "", "electricity_db", 3307);

    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
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
  <style>
    #togglePassword {
      background: #f8fafc;
      border: 1.5px solid #e2e8f0;
      border-left: none;
      border-radius: 0 10px 10px 0;
      padding: 0 12px;
      cursor: pointer;
      transition: background 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    #togglePassword:hover {
      background: #e2e8f0;
    }
    #togglePassword img {
      width: 22px;
      height: 22px;
      opacity: 0.6;
      transition: opacity 0.2s;
    }
    #togglePassword:hover img {
      opacity: 1;
    }
  </style>
</head>
<body class="login-page">

<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body p-4">

          <h4 class="text-center mb-1">⚡ Electricity Billing</h4>
          <p class="text-center text-muted mb-4">Sign in to continue</p>

          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control"
                     placeholder="Enter username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input type="password" name="password" id="password"
                       class="form-control" placeholder="Enter password" required>
                <button type="button" id="togglePassword">
                  <img src="images/eye-closed.png" id="eyeIcon" alt="Show password">
                </button>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
const toggleBtn   = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const eyeIcon     = document.getElementById('eyeIcon');

// Image paths
const eyeOpen   = 'images/eye-open.png';
const eyeClosed = 'images/eye-closed.png';

toggleBtn.addEventListener('click', function() {
    const isHidden = passwordInput.type === 'password';

    // Toggle input type
    passwordInput.type = isHidden ? 'text' : 'password';

    // Swap icon
    eyeIcon.src = isHidden ? eyeOpen : eyeClosed;
    eyeIcon.alt = isHidden ? 'Hide password' : 'Show password';
});
</script>

</body>
</html>