<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();
$deliveryId = (int) ($_GET['id'] ?? 0);
$pharmacyId = current_pharmacy_id();

$stmt = $pdo->prepare('SELECT * FROM deliveries WHERE id = ? AND is_active = 1 AND pharmacy_id = ?');
$stmt->execute([$deliveryId, $pharmacyId]);
$delivery = $stmt->fetch();

if (!$delivery) {
    header('Location: deliveries.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token.';
    } else {
        $newStatus = $_POST['status'] ?? $delivery['status'];
        $note = trim($_POST['note'] ?? '');
        $updateStmt = $pdo->prepare('UPDATE deliveries SET status = ?, updated_at = NOW() WHERE id = ? AND pharmacy_id = ?');
        $updateStmt->execute([$newStatus, $deliveryId, $pharmacyId]);
        $eventStmt = $pdo->prepare('INSERT INTO delivery_events (delivery_id, event, note, created_by) VALUES (?, ?, ?, ?)');
        $eventStmt->execute([$deliveryId, $newStatus, $note ?: 'Status updated.', current_user()['id']]);
        $message = 'Delivery updated.';
        $stmt->execute([$deliveryId, $pharmacyId]);
        $delivery = $stmt->fetch();
    }
}

$eventsStmt = $pdo->prepare('SELECT e.event, e.note, e.created_at, u.name FROM delivery_events e LEFT JOIN users u ON e.created_by = u.id WHERE e.delivery_id = ? ORDER BY e.created_at DESC');
$eventsStmt->execute([$deliveryId]);
$events = $eventsStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Delivery Details</h1>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Receipt <?php echo e($delivery['receipt_barcode']); ?></h5>
                <p class="mb-1"><strong>Customer:</strong> <?php echo e($delivery['customer_name']); ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?php echo e($delivery['customer_phone']); ?></p>
                <p class="mb-1"><strong>Address:</strong> <?php echo e($delivery['customer_address']); ?></p>
                <p class="mb-1"><strong>Taxi Driver:</strong> <?php echo e($delivery['taxi_driver_name']); ?> <?php echo e($delivery['taxi_driver_phone']); ?></p>
                <p class="mb-1"><strong>Delivery Fee:</strong> <?php echo e(number_format((float) $delivery['delivery_fee_amount'], 2)); ?> <?php echo e($delivery['delivery_fee_currency']); ?></p>
                <p class="mb-1"><strong>Payment Method:</strong> <?php echo e($delivery['payment_method']); ?></p>
                <p class="mb-1"><strong>Customer Payment:</strong> <?php echo e($delivery['customer_payment_status']); ?></p>
                <p class="mb-1"><strong>Taxi Collected:</strong> <?php echo e(number_format((float) $delivery['amount_collected_by_taxi'], 2)); ?> <?php echo e($delivery['amount_collected_currency']); ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary badge-status"><?php echo e($delivery['status']); ?></span></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Status</h5>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['created', 'given_to_taxi', 'delivered', 'money_collected', 'settled'] as $status): ?>
                                <option value="<?php echo e($status); ?>" <?php echo $delivery['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                    <button class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="card mt-3">
    <div class="card-body">
        <h5 class="card-title">Event Log</h5>
        <ul class="list-group list-group-flush">
            <?php foreach ($events as $event): ?>
                <li class="list-group-item">
                    <strong><?php echo e($event['event']); ?></strong> - <?php echo e($event['note']); ?><br>
                    <small class="text-muted">By <?php echo e($event['name'] ?? 'System'); ?> at <?php echo e($event['created_at']); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
