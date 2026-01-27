<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_admin();

$pdo = get_db_connection();
$message = '';
$exchangeRate = fetch_setting('exchange_rate', '1500');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid session token.';
    } else {
        $exchangeRate = trim($_POST['exchange_rate'] ?? '');
        if ($exchangeRate !== '') {
            upsert_setting('exchange_rate', $exchangeRate);
            $message = 'Exchange rate updated.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Settings</h1>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo e($message); ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Exchange Rate</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <div class="col-md-4">
                <label class="form-label">1 USD = IQD</label>
                <input type="number" name="exchange_rate" class="form-control" value="<?php echo e($exchangeRate); ?>" step="0.01" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
