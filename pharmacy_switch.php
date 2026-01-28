<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php');
    exit;
}

$pharmacyId = (int) ($_POST['pharmacy_id'] ?? 0);
$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT id FROM pharmacies WHERE id = ? AND status = "active"');
$stmt->execute([$pharmacyId]);
$pharmacy = $stmt->fetch();

if ($pharmacy) {
    start_secure_session();
    $_SESSION['pharmacy_id'] = $pharmacyId;
}

header('Location: dashboard.php');
exit;
