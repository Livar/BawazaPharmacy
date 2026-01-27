<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();

$staffHoursStmt = $pdo->query("SELECT u.name, SUM(TIMESTAMPDIFF(MINUTE, s.clock_in, COALESCE(s.clock_out, NOW())))/60 AS hours FROM staff_shifts s JOIN users u ON s.user_id = u.id GROUP BY u.name ORDER BY u.name");
$staffHours = $staffHoursStmt->fetchAll();

$taxiBalancesStmt = $pdo->query("SELECT taxi_driver_name, taxi_driver_phone, SUM(amount_collected_by_taxi) AS total_collected, amount_collected_currency FROM deliveries WHERE status != 'settled' AND is_active = 1 GROUP BY taxi_driver_name, taxi_driver_phone, amount_collected_currency ORDER BY taxi_driver_name");
$taxiBalances = $taxiBalancesStmt->fetchAll();

$deliverySummaryStmt = $pdo->query("SELECT status, COUNT(*) AS total FROM deliveries WHERE is_active = 1 GROUP BY status ORDER BY status");
$deliverySummary = $deliverySummaryStmt->fetchAll();

$cashHistoryStmt = $pdo->query("SELECT c.id, c.count_date, c.notes, SUM(i.denomination * i.quantity) AS total_amount, i.currency FROM cash_counts c JOIN cash_count_items i ON c.id = i.cash_count_id GROUP BY c.id, i.currency ORDER BY c.count_date DESC");
$cashHistory = $cashHistoryStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Reports</h1>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Staff Hours</h5>
            <a href="export.php?report=staff_hours" class="btn btn-sm btn-outline-primary">Export CSV</a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffHours as $row): ?>
                        <tr>
                            <td><?php echo e($row['name']); ?></td>
                            <td><?php echo e(number_format((float) $row['hours'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Taxi Balances (Unsettled)</h5>
            <a href="export.php?report=taxi_balances" class="btn btn-sm btn-outline-primary">Export CSV</a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Phone</th>
                        <th>Total Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taxiBalances as $row): ?>
                        <tr>
                            <td><?php echo e($row['taxi_driver_name']); ?></td>
                            <td><?php echo e($row['taxi_driver_phone']); ?></td>
                            <td><?php echo e(number_format((float) $row['total_collected'], 2)); ?> <?php echo e($row['amount_collected_currency']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Deliveries Summary</h5>
            <a href="export.php?report=deliveries_summary" class="btn btn-sm btn-outline-primary">Export CSV</a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliverySummary as $row): ?>
                        <tr>
                            <td><?php echo e($row['status']); ?></td>
                            <td><?php echo e($row['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Cash Count History</h5>
            <a href="export.php?report=cash_history" class="btn btn-sm btn-outline-primary">Export CSV</a>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Notes</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cashHistory as $row): ?>
                        <tr>
                            <td><?php echo e($row['count_date']); ?></td>
                            <td><?php echo e($row['notes']); ?></td>
                            <td><?php echo e(number_format((float) $row['total_amount'], 2)); ?> <?php echo e($row['currency']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
