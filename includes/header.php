<?php
require_once __DIR__ . '/functions.php';
start_secure_session();
$user = current_user();
$exchange_rate = fetch_setting('exchange_rate', '1500');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><?php echo e(APP_NAME); ?></a>
        <?php if ($user): ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <ul class="navbar-nav flex-row flex-wrap gap-2 mb-0">
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="deliveries.php">Deliveries</a></li>
                        <li class="nav-item"><a class="nav-link" href="cash_counts.php">Cash Counts</a></li>
                        <li class="nav-item"><a class="nav-link" href="staff.php">Staff</a></li>
                        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="clock.php">Clock In/Out</a></li>
                </ul>
                <span class="navbar-text text-white">
                    Rate: 1 USD = <?php echo e($exchange_rate); ?> IQD
                </span>
                <span class="navbar-text text-white">
                    <?php echo e($user['name']); ?> (<?php echo e($user['role']); ?>)
                </span>
                <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container py-4">
