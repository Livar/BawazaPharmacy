<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();
$report = $_GET['report'] ?? '';

$filename = 'report.csv';
$rows = [];
$headers = [];

switch ($report) {
    case 'staff_hours':
        $headers = ['Staff Member', 'Total Hours'];
        $stmt = $pdo->query("SELECT u.name, SUM(TIMESTAMPDIFF(MINUTE, s.clock_in, COALESCE(s.clock_out, NOW())))/60 AS hours FROM staff_shifts s JOIN users u ON s.user_id = u.id GROUP BY u.name ORDER BY u.name");
        $rows = $stmt->fetchAll();
        $filename = 'staff_hours.csv';
        break;
    case 'taxi_balances':
        $headers = ['Driver', 'Phone', 'Total Collected', 'Currency'];
        $stmt = $pdo->query("SELECT taxi_driver_name, taxi_driver_phone, SUM(amount_collected_by_taxi) AS total_collected, amount_collected_currency FROM deliveries WHERE status != 'settled' AND is_active = 1 GROUP BY taxi_driver_name, taxi_driver_phone, amount_collected_currency ORDER BY taxi_driver_name");
        $rows = $stmt->fetchAll();
        $filename = 'taxi_balances.csv';
        break;
    case 'deliveries_summary':
        $headers = ['Status', 'Total'];
        $stmt = $pdo->query("SELECT status, COUNT(*) AS total FROM deliveries WHERE is_active = 1 GROUP BY status ORDER BY status");
        $rows = $stmt->fetchAll();
        $filename = 'deliveries_summary.csv';
        break;
    case 'cash_history':
        $headers = ['Date', 'Notes', 'Total', 'Currency'];
        $stmt = $pdo->query("SELECT c.count_date, c.notes, SUM(i.denomination * i.quantity) AS total_amount, i.currency FROM cash_counts c JOIN cash_count_items i ON c.id = i.cash_count_id GROUP BY c.id, i.currency ORDER BY c.count_date DESC");
        $rows = $stmt->fetchAll();
        $filename = 'cash_history.csv';
        break;
    default:
        header('Location: reports.php');
        exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
if ($headers) {
    fputcsv($output, $headers);
}
foreach ($rows as $row) {
    fputcsv($output, array_values($row));
}
fclose($output);
exit;
