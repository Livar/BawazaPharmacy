<?php
/** @var array|null $currentUser */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($appName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light app-shell">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?php echo e(routeUrl('dashboard')); ?>"><?php echo e($appName); ?></a>
        <?php if ($currentUser): ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <ul class="navbar-nav flex-row flex-wrap gap-2 mb-0">
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('dashboard')); ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('deliveries')); ?>">Deliveries</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('cash_counts')); ?>">Cash Counts</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('staff')); ?>">Staff</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('pharmacies')); ?>">Pharmacies</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('reports')); ?>">Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('settings')); ?>">Settings</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(routeUrl('clock')); ?>">Clock In/Out</a></li>
                </ul>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <form method="post" action="<?php echo e(routeUrl('pharmacy_switch')); ?>" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                        <select name="pharmacy_id" class="form-select form-select-sm">
                            <?php foreach ($pharmacies as $pharmacy): ?>
                                <option value="<?php echo e((string) $pharmacy['id']); ?>" <?php echo $currentPharmacyId === (int) $pharmacy['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($pharmacy['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-light btn-sm" type="submit">Switch</button>
                    </form>
                <?php endif; ?>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm position-relative dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Notifications
                        <?php if ($notificationCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo e((string) $notificationCount); ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 280px;">
                        <?php if (!$notifications): ?>
                            <div class="text-muted small">No notifications yet.</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $item): ?>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="small">
                                        <?php echo e($item['message']); ?>
                                        <div class="text-muted small"><?php echo e($item['created_at']); ?></div>
                                    </div>
                                    <a class="btn btn-sm btn-outline-primary ms-2" href="<?php echo e(routeUrl('notification_read', ['id' => $item['id'], 'redirect' => $item['link'] ?? routeUrl('dashboard')])); ?>">View</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-white small text-end">
                    <div>Rate: 1 USD = <?php echo e($exchangeRate); ?> IQD</div>
                    <div><?php echo e($currentUser['name']); ?> (<?php echo e($currentUser['role']); ?>)</div>
                </div>
                <a class="btn btn-outline-light btn-sm" href="<?php echo e(routeUrl('logout')); ?>">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container py-4">
