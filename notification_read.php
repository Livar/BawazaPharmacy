<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$notificationId = (int) ($_GET['id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'dashboard.php';
$pharmacyId = current_pharmacy_id();

if ($notificationId && $pharmacyId) {
    $service = new Notifications(get_db_connection());
    $service->markRead($notificationId, $pharmacyId);
}

header('Location: ' . $redirect);
exit;
