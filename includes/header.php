<?php require_once __DIR__ . '/functions.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Electricity Billing System</title>
    
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Your Custom CSS -->
    <link rel="stylesheet" href="<?= app_url('/css/style.css'); ?>"> 
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Corrected PHP helper call for the brand link -->
            <a class="navbar-brand" href="<?= app_url('/'); ?>">E‑Billing</a>
        </div>
    </nav>

    <main class="container py-4">
        <!-- Your content goes here -->
    </main>

    <!-- Bootstrap JS (Optional but recommended) -->
    <script src="https://cdn.jsdelivr.net"></script>
</body>
</html>
