<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO deliveries (receipt_barcode, customer_name, customer_phone, customer_address, taxi_driver_name, taxi_driver_phone, delivery_fee_amount, delivery_fee_currency, payment_method, customer_payment_status, amount_collected_by_taxi, amount_collected_currency, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['receipt_barcode'] ?? ''),
            trim($_POST['customer_name'] ?? ''),
            trim($_POST['customer_phone'] ?? ''),
            trim($_POST['customer_address'] ?? ''),
            trim($_POST['taxi_driver_name'] ?? ''),
            trim($_POST['taxi_driver_phone'] ?? ''),
            (float) ($_POST['delivery_fee_amount'] ?? 0),
            $_POST['delivery_fee_currency'] ?? 'IQD',
            $_POST['payment_method'] ?? 'cash',
            $_POST['customer_payment_status'] ?? 'paid',
            (float) ($_POST['amount_collected_by_taxi'] ?? 0),
            $_POST['amount_collected_currency'] ?? 'IQD',
            'created',
            current_user()['id'],
        ]);
        $deliveryId = $pdo->lastInsertId();
        $eventStmt = $pdo->prepare('INSERT INTO delivery_events (delivery_id, event, note, created_by) VALUES (?, ?, ?, ?)');
        $eventStmt->execute([$deliveryId, 'created', 'Delivery created.', current_user()['id']]);
        $message = 'Delivery created successfully.';
    }
}

$deliveriesStmt = $pdo->query("SELECT id, receipt_barcode, customer_name, taxi_driver_name, delivery_fee_amount, delivery_fee_currency, status, created_at FROM deliveries WHERE is_active = 1 ORDER BY created_at DESC LIMIT 50");
$deliveries = $deliveriesStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Deliveries</h1>
<?php if ($message): ?>
    <div class="alert alert-info"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">New Delivery</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <div class="col-md-4">
                <label class="form-label">Receipt Barcode</label>
                <input type="text" name="receipt_barcode" class="form-control" placeholder="Scan barcode" required autofocus>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer Phone</label>
                <input type="text" name="customer_phone" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Customer Address</label>
                <input type="text" name="customer_address" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Taxi Driver Name</label>
                <input type="text" name="taxi_driver_name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Taxi Driver Phone</label>
                <input type="text" name="taxi_driver_phone" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Fee</label>
                <input type="number" step="0.01" name="delivery_fee_amount" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select name="delivery_fee_currency" class="form-select">
                    <option value="IQD">IQD</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="fib">FIB</option>
                    <option value="qi">QI</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer Payment Status</label>
                <select name="customer_payment_status" class="form-select">
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="taxi_collects">Taxi Collects</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Amount Collected by Taxi</label>
                <input type="number" step="0.01" name="amount_collected_by_taxi" class="form-control" value="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select name="amount_collected_currency" class="form-select">
                    <option value="IQD">IQD</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save Delivery</button>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Recent Deliveries</h5>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Customer</th>
                        <th>Taxi</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveries as $delivery): ?>
                        <tr>
                            <td><a href="delivery_view.php?id=<?php echo e((string) $delivery['id']); ?>"><?php echo e($delivery['receipt_barcode']); ?></a></td>
                            <td><?php echo e($delivery['customer_name']); ?></td>
                            <td><?php echo e($delivery['taxi_driver_name']); ?></td>
                            <td><?php echo e(number_format((float) $delivery['delivery_fee_amount'], 2)); ?> <?php echo e($delivery['delivery_fee_currency']); ?></td>
                            <td><span class="badge bg-secondary badge-status"><?php echo e($delivery['status']); ?></span></td>
                            <td><?php echo e($delivery['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
