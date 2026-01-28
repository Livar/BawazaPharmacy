<?php
$target = 'index.php';
$query = $_SERVER['QUERY_STRING'] ?? '';
$routeMap = [
    'dashboard.php' => 'dashboard',
    'deliveries.php' => 'deliveries',
    'delivery_view.php' => 'delivery_view',
    'cash_counts.php' => 'cash_counts',
    'staff.php' => 'staff',
    'reports.php' => 'reports',
    'settings.php' => 'settings',
    'pharmacies.php' => 'pharmacies',
    'pharmacy_switch.php' => 'pharmacy_switch',
    'clock.php' => 'clock',
    'export.php' => 'export',
    'logout.php' => 'logout',
    'notification_read.php' => 'notification_read',
];
$route = $routeMap[basename(__FILE__)] ?? 'login';
$target .= '?route=' . $route;
if ($query) {
    $target .= '&' . $query;
}
header('Location: ' . $target);
exit;
